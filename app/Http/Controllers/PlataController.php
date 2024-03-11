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
            // Daca nu se gaseste plata in DB
            if (!($plata = Plata::where('banca_order_id', $orderId)->first())){
                echo 'Nu am găsit în sistem această comandă. Dacă plata ta a fost procesată, și banii ți-au fost luați din cont, te rugăm să ne comunici, pentru a corecta comanda. Mulțumim';
                die();
            }
        }

        echo "Back from the bank interface";

        // dd($request);

        $this->actualizareDetaliiPlataDinContBT($orderId, $plata);

        // return view('plati.guest.adaugaPlataPasul2', compact('plata'));
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
            $plata->orderDescription =  $json_data['orderDescription'];

        switch ($plata->order_status){
            case '0':
                $plata->order_status_description = 'Autovehiculul a fost înregistrat, dar plata nu a fost finalizată';
            case '1':
                $plata->order_status_description = 'preautorizata';
            case '2':
                $plata->order_status_description = 'Autovehiculul a fost înregistrat și plata s-a efectuat cu success!';
            case '3':
                $plata->order_status_description = 'anulata';
            case '4':
                $plata->order_status_description = 'rambursata';
            case '5':
                $plata->order_status_description = 'asteptare3ds';
            case '6':
                $plata->order_status_description = 'Autovehiculul a fost înregistrat, dar tranzacția a fost declinată din diferite motive (Card blocat, fonduri insuficiente, limită tranzacționare depășită, CVV greșit, card expirat, banca emitentă a deținătorului de card a declinat tranzacția, etc.)';
            case '7':
                $plata->order_status_description = 'rambursata_partial';
        }
        
        switch ($plata->actionCode){
            case '0':
                $plata->action_code_description = 'Plata s-a efectuat cu succes.';
            case '104':
                $plata->action_code_description = 'Card restricționat (blocat temporar sau permanent din cauza lipsei plății sau a morții titularului de card).';
            case '124':
                $plata->action_code_description = 'Tranzacția nu poate fi autorizată din cauza acordului guvernului, băncii centrale sau instituției financiare, legi sau reglementări.';
            case '320':
                $plata->action_code_description = 'Card inactiv. Vă rugăm activați cardul.';
            case '801':
                $plata->action_code_description = 'Emitent indisponibil.';
            case '803':
                $plata->action_code_description = 'Card blocat. Contactați banca emitentă sau reîncercați tranzacția cu alt card.';
            case '804':
                $plata->action_code_description = 'Tranzacția nu este permisă. Contactați banca emitentă sau reîncercați tranzacția cu alt card.';
            case '805':
                $plata->action_code_description = 'Tranzacție respinsă.';
            case '861':
                $plata->action_code_description = 'Dată expirare card greșită.';
            case '871':
                $plata->action_code_description = 'CVV gresit.';
            case '905':
                $plata->action_code_description = 'Card invalid. Acesta nu există în baza de date.';
            case '906':
                $plata->action_code_description = 'Card expirat.';
            case '913':
                $plata->action_code_description = 'Tranzacție invalidă. Contactați banca emitentă sau reîncercați tranzacția cu alt card.';
            case '914':
                $plata->action_code_description = 'Cont invalid. Vă rugăm contactați banca emitentă.';
            case '915':
                $plata->action_code_description = 'Fonduri insuficiente.';
            case '917':
                $plata->action_code_description = 'Limită tranzacționare depășită.';
            case '952':
                $plata->action_code_description = 'Suspect de fraudă.';
            case '998':
                $plata->action_code_description = 'Tranzacția în rate nu este permisă cu acest card. Te rugăm să folosești un card de credit emis de Banca Transilvania.';
            case '341016':
                $plata->action_code_description = '3DS2 authentication is declined by Authentication Response (ARes) – issuer';
            case '341017':
                $plata->action_code_description = '3DS2 authentication status in ARes is unknown - issuer';
            case '341018':
                $plata->action_code_description = '3DS2 CReq cancelled - client';
            case '341019':
                $plata->action_code_description = '3DS2 CReq failed - client/issuer';
            case '341020':
                $plata->action_code_description = '3DS2 unknown status in RReq - issuer';
            default:
                $plata->action_code_description = 'Tranzacție refuzată, vă rugăm reîncercați.';
        }

        $plata->update();

        dd($json_data);
    }
}
