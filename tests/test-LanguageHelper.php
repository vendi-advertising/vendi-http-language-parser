<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper\UnitTests;

use PHPUnit\Framework\TestCase;
use Vendi\HttpLanguageHelper\LanguageHelper;
use Vendi\Shared\utils;

/**
 * @covers \Vendi\HttpLanguageHelper\LanguageHelper
 */
class test_LanguageHelper extends TestCase
{
    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageHelper::get_server_language_from_client_string
     */
    public function test__get_server_language_from_client_string()
    {
        $this->assertSame('ch', LanguageHelper::get_server_language_from_client_string('ch', [ 'en', 'de' ], ''));
        $this->assertSame('ch', LanguageHelper::get_server_language_from_client_string('ch', [ ], 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'));

        $this->assertSame('fr-CH', LanguageHelper::get_server_language_from_client_string('ch', [ 'fr-CH', 'fr' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH'));
        $this->assertSame('fr', LanguageHelper::get_server_language_from_client_string('ch', [ 'en', 'fr' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH'));


        $this->assertSame('ch', LanguageHelper::get_server_language_from_client_string('ch', [ 'gf', 'zi' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH'));

        $this->assertSame('de', LanguageHelper::get_server_language_from_client_string('ch', [ 'en', 'de' ], 'de'));
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageHelper::detect_client_preference
     * @dataProvider provider_for_test__detect_client_preference
     */
    public function test__detect_client_preference(string $expected_value, string $default_server_language, array $all_server_languages, array $server = null, array $post = null, array $get = null, array $cookies = null)
    {
        utils::reset_all_custom_arrays();

        if ($server) {
            utils::$CUSTOM_SERVER = $server;
        }

        if ($post) {
            utils::$CUSTOM_POST = $post;
        }

        if ($get) {
            utils::$CUSTOM_GET = $get;
        }

        if ($cookies) {
            utils::$CUSTOM_COOKIE = $cookies;
        }

        $this->assertSame($expected_value, LanguageHelper::detect_client_preference($default_server_language, $all_server_languages));
    }

    public function provider_for_test__detect_client_preference()
    {
        return [
                    [ 'de', 'en', ['en', 'de'], ['REQUEST_METHOD' => 'POST' ], [LanguageHelper::DEFAULT_KEY_VALUE_FOR_POST => 'de'] ],
                    [ 'en', 'en', ['en', 'de'], ['REQUEST_METHOD' => 'POST' ], [LanguageHelper::DEFAULT_KEY_VALUE_FOR_POST => 'es'] ],
                    [ 'de', 'en', ['en', 'de'], ['HTTP_ACCEPT_LANGUAGE' => 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH' ], [], [], [LanguageHelper::DEFAULT_KEY_VALUE_FOR_COOKIE => 'de'] ],
        ];
    }
}
