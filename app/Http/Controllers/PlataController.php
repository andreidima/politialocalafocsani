<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Plata;
use App\Models\Tarif;

use Carbon\Carbon;

class PlataController extends Controller
{
    /**
     * Show the step 0 Form for creating a new 'plata'.
     *
     * @return \Illuminate\Http\Response
     */
    public function adaugaPlataNoua(Request $request)
    {
        if(!empty($request->session()->get('plata'))){
            $request->session()->forget('plata');
        }

        $plata = new Plata();

        $request->session()->put('plata', $plata);

        return redirect('/plati/adauga-plata-pasul-1');
    }

    /**
     * Show the step 1 Form for creating a new 'plata'.
     *
     * @return \Illuminate\Http\Response
     */
    public function adaugaPlataPasul1(Request $request)
    {
        if(empty($request->session()->get('plata'))){
            return redirect('/plati/adauga-plata-noua');
        } else {
            $plata = $request->session()->get('plata');
        }

        $tarife = Tarif::all();

        return view('plati.guest.adaugaPlataPasul1', compact('plata', 'tarife'));
    }

    /**
     * Post Request to store step1 info in session
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAdaugaPlataPasul1(Request $request)
    {
        if(empty($request->session()->get('plata'))){
            return redirect('/plati/adauga-plata-noua');
        } else {
            $plata = $request->session()->get('plata');
        }

        $plata = new Plata();

        $plata->fill(
            $request->validate([
                'tarif_id' => 'required',
                'data_inceput' => 'required|date|after:yesterday',
                'nr_inmatriculare' => 'required',
                // 'email' => 'nullable|max:255|email:rfc,dns',
                // 'telefon' => 'max:255',
                // 'gdpr' => 'required',
            ],
            [
                'tarif_id.required' => 'Câmpul Categorie/durata este obligatoriu.',
            ]
            )
        );

        $plata->nr_inmatriculare = preg_replace("/[^a-zA-Z0-9]+/", "", $plata->nr_inmatriculare);

        $tarif = Tarif::where('id', $plata->tarif_id)->first();
        $plata->pret = $tarif->pret;
        switch ($tarif->durata) {
            case ('Taxă specială de acces pentru o zi'):
                $plata->data_sfarsit = $plata->data_inceput;
                break;
            case ('Taxă specială de acces pentru o lună'):
                $plata->data_sfarsit = Carbon::parse($plata->data_inceput)->addMonth()->subDay();
                break;
            case ('Taxă specială de acces pentru un an'):
                $plata->data_sfarsit = Carbon::parse($plata->data_inceput)->addYear()->subDay();
                break;
            default:
                return back()->with('error', 'Nu se poate seta perioada durabilității. Contactați administratorul aplicației.');
        }

        $request->session()->put('plata', $plata);

        return redirect('/plati/adauga-plata-pasul-2');
    }

    /**
     * Show the step 2 Form for creating a new 'plata'.
     *
     * @return \Illuminate\Http\Response
     */
    public function adaugaPlataPasul2(Request $request)
    {
        if(empty($request->session()->get('plata'))){
            return redirect('/plati/adauga-plata-noua');
        } else {
            $plata = $request->session()->get('plata');
        }

        return view('plati.guest.adaugaPlataPasul2', compact('plata'));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postAdaugaPlataPasul2(Request $request)
    {
        if(empty($request->session()->get('plata'))){
            return redirect('/plati/adauga-plata-noua');
        } else {
            $plata = $request->session()->get('plata');
        }

        // Daca s-a ajuns cumva aici fara sa fie salvate datele corect, si plata nu are setat pretul, se intoarce inapoi
        if (!$plata->pret) {
            return redirect('/plati/adauga-plata-noua');
        }

        $order_data_a = array(
                        "userName=".config('bancaTransilvania.userName', ''),
                        "password=".config('bancaTransilvania.password', ''),
                        // "orderNumber=".$plata->id,
                        "orderNumber=".uniqid(),
                        // "amount=".$plata->pret,
                        // "amount=".($plata->pret/10),
                        "amount=".($plata->pret/10),
                        "currency=946",
                        "returnUrl=https://politialocalafocsani.validsoftware.eu/plati/adauga-plata-pasul-3",
                        "description=Plata pentru accesul autovehiculelor de transport greu in Focsani. Categoria: ".$plata->tarif->categorie.". Durata: ".$plata->tarif->durata,
                        // 'pageView=DESKTOP'
                );
        $order_data = implode("&", $order_data_a);

        $register_endpoint = 'https://ecclients.btrl.ro:5443/payment/rest/register.do';
        // $register_endpoint = 'https://ecclients.btrl.ro/payment/rest/register.do';

        //call API
        $ch = curl_init();//open connection
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_URL,$register_endpoint);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $order_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $order_result = curl_exec($ch);
        // dd($order_result);

        $result_ibtpay = json_decode($order_result);
        // dd($result_ibtpay);

        $ibtpay_url = $result_ibtpay->formUrl ?? null;

        curl_close($ch);

        $plata->banca_order_id = $result_ibtpay->orderId;
        $plata->save();

        if ($ibtpay_url){
            // header("Location: http://www.example.com/");
            header('Location:' . $ibtpay_url);
            exit;
        } else {
            return back()->with('error', 'Nu s-a putut initia plata');
        }
    }

    /**
     * Show the step 3 Form for creating a new 'plata'.
     *
     * @return \Illuminate\Http\Response
     */
    public function adaugaPlataPasul3(Request $request)
    {
        // if(empty($request->session()->get('plata'))){
        //     return redirect('/plati/adauga-plata-noua');
        // } else {
        //     $plata = $request->session()->get('plata');
        // }

        echo "Back from the bank interface";


        $orderId = $_GET['orderId'];
        $plata = Plata::where('banca_order_id', $orderId)->first();

        if (!$plata) {
            echo 'Nu am găsit în sistem această comandă. Dacă plata ta a fost procesată, și banii ți-au fost luați din cont, te rugăm să ne comunici, pentru a corecta comanda. Mulțumim';
            die();
        }

        dd($_GET['orderId'], $request);

        // return view('plati.guest.adaugaPlataPasul2', compact('plata'));
    }
}
