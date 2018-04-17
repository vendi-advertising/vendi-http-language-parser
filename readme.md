# Vendi HTTP Language Helper

## Installation

    composer require vendi-advertising/vendi-http-language-parser

## Usage

    LanguageHelper::get_server_language_from_client_string(
        string $default_server_language,
        array $one_or_more_server_languages,
        string $client_language_string
    );

## Examples
    $lang = LanguageHelper::get_server_language_from_client_string( 'en', [ 'en', 'de' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH' );
    //$lang = 'en'  //English is weighted more than German

    $lang = LanguageHelper::get_server_language_from_client_string( 'en', [ 'en', 'fr' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH' );
    //$lang = 'fr' //Languages without a weight default to 1
