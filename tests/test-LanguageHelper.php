<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper\UnitTests;

use PHPUnit\Framework\TestCase;
use Vendi\HttpLanguageHelper\LanguageHelper;

/**
 * @covers \Vendi\HttpLanguageHelper\LanguageHelper
 */
class test_LanguageHelper extends TestCase
{
    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageHelper::get_server_language_from_client_string
     */
    public function test_get_server_language_from_client_string()
    {
        $this->assertSame('ch', LanguageHelper::get_server_language_from_client_string('ch', [ 'en', 'de' ], ''));
        $this->assertSame('ch', LanguageHelper::get_server_language_from_client_string('ch', [ ], 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5'));

        $this->assertSame('fr-CH', LanguageHelper::get_server_language_from_client_string('ch', [ 'fr-CH', 'fr' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH'));
        $this->assertSame('fr', LanguageHelper::get_server_language_from_client_string('ch', [ 'en', 'fr' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH'));


        $this->assertSame('ch', LanguageHelper::get_server_language_from_client_string('ch', [ 'gf', 'zi' ], 'en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH'));
    }
}
