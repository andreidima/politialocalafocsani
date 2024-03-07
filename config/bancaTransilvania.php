<?php

return [

    'userName' => env('BANCA_TRANSILVANIA_USERNAME', ''),
    'password' => env('BANCA_TRANSILVANIA_PASSWORD', ''),
    'registerEndpoint' => env('BANCA_TRANSILVANIA_REGISTER_ENDPOINT', ''),
    'getOrderStatusEndpoint' => env('BANCA_TRANSILVANIA_GET_ORDER_STATUS_ENDPOINT', ''),
    'getFinisedPaymentInfoEndpoint' => env('BANCA_TRANSILVANIA_GET_FINISHED_PAYMENT_INFO_ENDPOINT', ''),

];
