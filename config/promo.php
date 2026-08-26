<?php

return [
    /*
    | Globalni promo kod za QR kodove u teretanama. Kampanja krece 1.9.2026.
    | Sam zapis koda (trajanje, datum pocetka, limiti) zivi u tabeli promo_codes -
    | ovde je samo koji kod landing stranica nudi po defaultu.
    */
    'default_code' => env('PROMO_DEFAULT_CODE', 'GYM30'),

    'stores' => [
        'ios' => 'https://apps.apple.com/rs/app/fity-meals/id6753711257',
        'android' => 'https://play.google.com/store/apps/details?id=app.getfity',
    ],

    'apps' => [
        'ios_app_id' => '6753711257',
        'ios_bundle_id' => 'app.fity',
        'ios_team_id' => 'VNM8M8L6AH',
        'android_package' => 'app.getfity',
    ],

    /*
    | SHA-256 otisak kljuca kojim Google potpisuje aplikaciju. Nalazi se u
    | Play Console > Test and release > Setup > App integrity > App signing key
    | certificate. Bez njega Android ne moze da verifikuje App Link i link ce se
    | otvarati u browseru umesto u aplikaciji. Vise otisaka razdvojiti zarezom.
    */
    'android_sha256_fingerprints' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ANDROID_SHA256_FINGERPRINTS', ''))
    ))),
];
