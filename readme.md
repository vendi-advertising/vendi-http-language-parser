# Vendi HTTP Language Helper

[![Build Status](https://travis-ci.org/vendi-advertising/vendi-http-language-parser.svg?branch=master)](https://travis-ci.org/vendi-advertising/vendi-http-language-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/vendi-advertising/vendi-http-language-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/vendi-advertising/vendi-http-language-parser/?branch=master)


## Installation

    composer require vendi-advertising/vendi-http-language-parser

### LanguageHelper::get_server_language_from_client_string()
This static method will return the first value from the array `$one_or_more_server_languages` that matches various values from the HTTP state.

#### Usage

    use Vendi\HttpLanguageHelper\LanguageHelper;

    LanguageHelper::detect_client_preference(
        string $default_server_language,
        array $all_server_languages,
        array $keys = null
    );

#### Examples
    use Vendi\HttpLanguageHelper\LanguageStringParser;

    $lang = LanguageHelper::detect_client_preference( 'en', [ 'en', 'de' ] );
    //$lang = 'en'  //English is the default

    //Assuming that the user has their HTTP_ACCEPT_LANGUAGE set to:
    // en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH
    $lang = LanguageHelper::get_server_language_from_client_string( 'en', [ 'en', 'fr' ]);
    //$lang = 'fr' //French is weight strongest in the user preference

    //Assuming a POST was made with lang=fr
    $lang = LanguageHelper::get_server_language_from_client_string( 'en', [ 'en', 'fr' ]);
    //$lang = 'fr' //French was found in the post

    //Assuming a GET was made with lang=fr
    $lang = LanguageHelper::get_server_language_from_client_string( 'en', [ 'en', 'fr' ]);
    //$lang = 'fr' //French was found in the post

    //The order for search is POST, GET, COOKIE and finally user's preference via the
    //server variable HTTP_ACCEPT_LANGUAGE

### LanguageHelper::get_server_language_from_client_string()
This static method will return the first value from the array `$one_or_more_server_languages` that matches the [RFC 7231](https://tools.ietf.org/html/rfc7231#section-5.3.5) string string passed as `$client_language_string`.

#### Usage

    use Vendi\HttpLanguageHelper\LanguageHelper;

    LanguageHelper::get_server_language_from_client_string(
        string $default_server_language,
        array $one_or_more_server_languages,
        string $client_language_string
    );

#### Examples
    use Vendi\HttpLanguageHelper\LanguageStringParser;

    $lang = LanguageHelper::get_server_language_from_client_string( 'en', [ 'en', 'de' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH' );
    //$lang = 'en'  //English is weighted more than German

    $lang = LanguageHelper::get_server_language_from_client_string( 'en', [ 'en', 'fr' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH' );
    //$lang = 'fr' //Languages without a weight default to 1
