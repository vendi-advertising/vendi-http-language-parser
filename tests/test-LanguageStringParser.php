<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageParser\UnitTests;

use PHPUnit\Framework\TestCase;
use Vendi\HttpLanguageParser\LanguageStringParser;
use Vendi\HttpLanguageParser\Language;

class test_LanguageStringParser extends TestCase
{
    /**
     * @covers \Vendi\HttpLanguageParser\LanguageStringParser::__construct
     * @covers \Vendi\HttpLanguageParser\LanguageStringParser::get_languages
     */
    public function test____construct()
    {
        $parser = new LanguageStringParser('');
        $this->assertCount(0, $parser->get_languages());

        $parser = new LanguageStringParser('fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5');
        $this->assertCount(5, $parser->get_languages());

        $langs = $parser->get_languages();

        $this->__test_single(array_shift($langs), 1.0, 'fr', 'ch', 'fr-CH');
        $this->__test_single(array_shift($langs), 0.9,  'fr', null, 'fr;q=0.9');
        $this->__test_single(array_shift($langs), 0.8,  'en', null, 'en;q=0.8');
        $this->__test_single(array_shift($langs), 0.7,  'de', null, 'de;q=0.7');
        $this->__test_single(array_shift($langs), 0.5,  '*',  null, '*;q=0.5');
    }

    private function __test_single(Language $lang, $weight, $language, $variant, $original)
    {
        $this->assertSame($weight,   $lang->get_weight());
        $this->assertSame($language, $lang->get_language());
        $this->assertSame($variant,  $lang->get_variant());
        $this->assertSame($original, $lang->get_original());
    }
}
