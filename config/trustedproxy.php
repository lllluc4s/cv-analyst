<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Define os endereços IP que o Laravel deve considerar como proxies confiáveis.
    | Quando o seu aplicativo estiver por trás de um proxy como Nginx, Apache, etc.
    | isso permite que o Laravel obtenha o IP real do cliente a partir do cabeçalho
    | correto do proxy.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', '*'),

    /*
    |--------------------------------------------------------------------------
    | Cabeçalhos confiáveis de proxy
    |--------------------------------------------------------------------------
    |
    | Defina quais cabeçalhos HTTP para usar para recuperar dados como o IP do cliente,
    | endereço, protocolo, etc. dos proxies confiáveis.
    |
    */
    'headers' => [
        \Symfony\Component\HttpFoundation\Request::HEADER_FORWARDED => 'FORWARDED',
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR',
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST => 'X_FORWARDED_HOST',
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT => 'X_FORWARDED_PORT',
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
    ],

];
