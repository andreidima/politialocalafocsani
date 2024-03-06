<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PlataController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Auth::routes(['register' => false, 'password.request' => false, 'reset' => false]);


Route::redirect('/', '/acasa');

Route::get('/plati/adauga-plata-noua', [PlataController::class, 'adaugaPlataNoua']);
Route::get('/plati/adauga-plata-pasul-1', [PlataController::class, 'adaugaPlataPasul1']);
Route::post('/plati/adauga-plata-pasul-1', [PlataController::class, 'postAdaugaPlataPasul1']);
Route::get('/plati/adauga-plata-pasul-2', [PlataController::class, 'adaugaPlataPasul2']);
Route::post('/plati/adauga-plata-pasul-2', [PlataController::class, 'postAdaugaPlataPasul2']);
Route::get('/plati/adauga-plata-pasul-3', [PlataController::class, 'adaugaPlataPasul3']);
Route::post('/plati/adauga-plata-pasul-3', [PlataController::class, 'postAdaugaPlataPasul3']);
Route::get('/plati/adauga-plata-pasul-4', [PlataController::class, 'adaugaPlataPasul4']);


Route::group(['middleware' => 'auth'], function () {
    Route::view('/acasa', 'acasa');
});



// Teste politia locala

    Route::get('politia', function () {
        echo 'here I am 2';

        $order_data_a = array(
                        // "userName=test_iPay3_api",
                        // "password=test_iPay3_ap!e4r",
                        "userName=PolitiaLocalaMunFoc_API",
                        "password=cCjFW94qQV_Ap!",
                        "orderNumber=135271",
                        "email=andrei.dima@usm.ro",
                        "amount=15",
                        "currency=946",
                        "returnUrl=https://andreidimatest.ro/finish.html",
                        "description=Comanda 1 prin iPay BT la:",
                        'pageView=DESKTOP'
                );
        $order_data = implode("&", $order_data_a);

        // $register_endpoint = 'https://ecclients.btrl.ro:5443/payment/rest/register.do';
        $register_endpoint = 'https://ecclients.btrl.ro/payment/rest/register.do';

        //call API
        $ch = curl_init();//open connection
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_URL,$register_endpoint);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $order_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $order_result = curl_exec($ch);
        echo $order_result;

        $result_ibtpay = json_decode($order_result);

        $ibtpay_url = $result_ibtpay->formUrl ?? null;

        curl_close($ch);

        if ($ibtpay_url){
            // header("Location: http://www.example.com/");
            header('Location:' . $ibtpay_url);
            exit;
        }


    });
