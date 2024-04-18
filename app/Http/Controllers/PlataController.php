<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Plata;
use App\Models\Tarif;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
                'nr_inmatriculare' => 'required|min:5',
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
            "orderNumber=".uniqid(),
            // "amount=".$plata->pret,
            "amount=5",
            "currency=946",
            // "returnUrl=https://politialocalafocsani.validsoftware.eu/plati/adauga-plata-pasul-3",
            "returnUrl=https://plati.politialocalafocsani.ro/plati/adauga-plata-pasul-3",
            "description=Plata pentru accesul autovehiculelor de transport greu in Focsani. Categoria: ".$plata->tarif->categorie.". Durata: ".$plata->tarif->durata,
            // 'pageView=DESKTOP'
        );
        $order_data = implode("&", $order_data_a);

        $register_endpoint = config('bancaTransilvania.registerEndpoint', '');

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
        // Daca nu s-a ajuns aici dupa ce s-a facut o plata, clientul este trimis inapoi in pagina de start
        if(!($orderId = $_GET['orderId'])){
            return redirect('/plati/adauga-plata-noua');
        } else {
            dd($orderId);
            // Daca nu se gaseste plata in DB
            if (!($plata = Plata::where('banca_order_id', $orderId)->first())){
                return view('plati.guest.adaugaPlataPasul3');
                // echo 'Nu am găsit în sistem această comandă. Dacă plata ta a fost procesată, și banii ți-au fost luați din cont, te rugăm să ne comunici, pentru a corecta comanda. Mulțumim';
                // die();
            }
        }

        $plata = $this->actualizareDetaliiPlataDinContBT($orderId, $plata);

        return view('plati.guest.adaugaPlataPasul3', compact('plata'));
    }

    public function actualizareDetaliiPlataDinContBT($orderId, Plata $plata) {
        $order_data_a = array(
            "userName=".config('bancaTransilvania.userName', ''),
            "password=".config('bancaTransilvania.password', ''),
            "orderId=$orderId"
        );
        $order_data = implode("&", $order_data_a);

        $getorderstatus_endpoint = config('bancaTransilvania.getOrderStatusEndpoint', '');

        $ch = curl_init();//open connection
        curl_setopt($ch,CURLOPT_URL,$getorderstatus_endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $order_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //handle curl errors
        if (!$order_result = curl_exec($ch)) {
                $curl_url = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
                $curl_respcode = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
                $curl_err = curl_error($ch);
                $order->add_order_note( sprintf( "curl error when accessing '%s' , HTTP code: '%s', error message '%s'", $curl_url,$curl_respcode, $curl_err ));
                error_log( print_r('curl error on order '.$order->get_id().' errormessage:'.$curl_err,true) );
                curl_close($ch);
        }
        curl_close($ch);

        $json_data = json_decode($order_result, true);


        if (array_key_exists('errorCode',$json_data))
            $plata->error_code =  $json_data['errorCode'];
        if (array_key_exists('errorMessage',$json_data))
            $plata->error_message =  $json_data['errorMessage'];
        if (array_key_exists('orderNumber',$json_data))
            $plata->order_number =  $json_data['orderNumber'];
        if (array_key_exists('orderStatus',$json_data))
            $plata->order_status =  $json_data['orderStatus'];
        if (array_key_exists('actionCode',$json_data))
            $plata->action_code =  $json_data['actionCode'];
        if (array_key_exists('actionCodeDescription',$json_data))
            $plata->action_code_description =  $json_data['actionCodeDescription'];
        if (array_key_exists('amount',$json_data))
            $plata->amount =  $json_data['amount'];
        if (array_key_exists('currency',$json_data))
            $plata->currency =  $json_data['currency'];
        if (array_key_exists('date',$json_data))
            $plata->date =  $json_data['date'];
        if (array_key_exists('orderDescription',$json_data))
            $plata->order_description =  $json_data['orderDescription'];

        switch ($plata->order_status){
            case '0':
                $plata->order_status_description = 'Autovehiculul a fost înregistrat, dar plata nu a fost finalizată';
                break;
            case '1':
                $plata->order_status_description = 'preautorizata';
                break;
            case '2':
                $plata->order_status_description = 'Autovehiculul a fost înregistrat și plata s-a efectuat cu succes!';
                break;
            case '3':
                $plata->order_status_description = 'anulata';
                break;
            case '4':
                $plata->order_status_description = 'rambursata';
                break;
            case '5':
                $plata->order_status_description = 'asteptare3ds';
                break;
            case '6':
                $plata->order_status_description = 'Autovehiculul a fost înregistrat, dar plata a fost declinată.';
                // din diferite motive (Card blocat, fonduri insuficiente, limită tranzacționare depășită, CVV greșit, card expirat, banca emitentă a deținătorului de card a declinat tranzacția, etc.
                break;
            case '7':
                $plata->order_status_description = 'rambursata_partial';
                break;
        }

        switch ($plata->action_code){
            case '0':
                $plata->action_code_description = 'Plata s-a efectuat cu succes.';
                break;
            case '104':
                $plata->action_code_description = 'Card restricționat (blocat temporar sau permanent din cauza lipsei plății sau a morții titularului de card).';
                break;
            case '124':
                $plata->action_code_description = 'Tranzacția nu poate fi autorizată din cauza acordului guvernului, băncii centrale sau instituției financiare, legi sau reglementări.';
                break;
            case '320':
                $plata->action_code_description = 'Card inactiv. Vă rugăm activați cardul.';
                break;
            case '801':
                $plata->action_code_description = 'Emitent indisponibil.';
                break;
            case '803':
                $plata->action_code_description = 'Card blocat. Contactați banca emitentă sau reîncercați tranzacția cu alt card.';
                break;
            case '804':
                $plata->action_code_description = 'Tranzacția nu este permisă. Contactați banca emitentă sau reîncercați tranzacția cu alt card.';
                break;
            case '805':
                $plata->action_code_description = 'Tranzacție respinsă.';
                break;
            case '861':
                $plata->action_code_description = 'Dată expirare card greșită.';
                break;
            case '871':
                $plata->action_code_description = 'CVV gresit.';
                break;
            case '905':
                $plata->action_code_description = 'Card invalid. Acesta nu există în baza de date.';
                break;
            case '906':
                $plata->action_code_description = 'Card expirat.';
                break;
            case '913':
                $plata->action_code_description = 'Tranzacție invalidă. Contactați banca emitentă sau reîncercați tranzacția cu alt card.';
                break;
            case '914':
                $plata->action_code_description = 'Cont invalid. Vă rugăm contactați banca emitentă.';
                break;
            case '915':
                $plata->action_code_description = 'Fonduri insuficiente.';
                break;
            case '917':
                $plata->action_code_description = 'Limită tranzacționare depășită.';
                break;
            case '952':
                $plata->action_code_description = 'Suspect de fraudă.';
                break;
            case '998':
                $plata->action_code_description = 'Tranzacția în rate nu este permisă cu acest card. Te rugăm să folosești un card de credit emis de Banca Transilvania.';
                break;
            case '341016':
                $plata->action_code_description = '3DS2 authentication is declined by Authentication Response (ARes) – issuer';
                break;
            case '341017':
                $plata->action_code_description = '3DS2 authentication status in ARes is unknown - issuer';
                break;
            case '341018':
                $plata->action_code_description = '3DS2 CReq cancelled - client';
                break;
            case '341019':
                $plata->action_code_description = '3DS2 CReq failed - client/issuer';
                break;
            case '341020':
                $plata->action_code_description = '3DS2 unknown status in RReq - issuer';
                break;
            default:
                $plata->action_code_description = 'Tranzacție refuzată, vă rugăm reîncercați.';
                break;
        }

        $plata->update();

        return $plata;
    }

    public function verificarePlataNumarInmatriculare(Request $request)
    {
        $searchNumarInmatriculare = $request->searchNumarInmatriculare;
        $plati = null;

        session()->forget('eroare');

        if (!$searchNumarInmatriculare){
            return view('plati.verificarePlataNumarInmatriculare', compact('plati', 'searchNumarInmatriculare'));
        } else if (strlen($searchNumarInmatriculare) < 5){
            session()->flash('eroare', 'Numărul de înmatriculare trebuie sa conțină minim 5 caractere');
            return view('plati.verificarePlataNumarInmatriculare', compact('plati', 'searchNumarInmatriculare'));
        } else {
            $searchNumarInmatriculare = preg_replace("/[^a-zA-Z0-9]+/", "", $searchNumarInmatriculare);
            $plati = Plata::where('nr_inmatriculare', $searchNumarInmatriculare)
                ->whereDate('data_sfarsit', '>=', Carbon::today())
                ->orderBy('data_inceput', 'desc')
                ->get();
        }

        // Daca sunt plati care nu au actualizate platile cu datele de la banca, se face reverificarea acum
        if ($plati){
            foreach ($plati as $plata) {
                // Daca a existat o conexiune cu banca, dar inca nu au fost aduse informatiile din contul bancar, se aduc acum
                if ($plata->banca_order_id && !$plata->order_status) {
                    $this->actualizareDetaliiPlataDinContBT($plata->banca_order_id, $plata);
                }
            }

        }

        return view('plati.verificarePlataNumarInmatriculare', compact('plati', 'searchNumarInmatriculare'));
    }
}
