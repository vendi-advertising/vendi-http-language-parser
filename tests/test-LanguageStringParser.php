<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper\UnitTests;

use PHPUnit\Framework\TestCase;
use Vendi\HttpLanguageHelper\Language;
use Vendi\HttpLanguageHelper\LanguageStringParser;

/**
 * @covers \Vendi\HttpLanguageHelper\LanguageStringParser
 */
class test_LanguageStringParser extends TestCase
{
    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::__construct
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::get_languages
     */
    public function test____construct()
    {
        $parser = new LanguageStringParser('');
        $this->assertCount(0, $parser->get_languages());

        $parser = new LanguageStringParser('fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5');
        $this->assertCount(5, $parser->get_languages());

        $langs = $parser->get_languages();

        $this->__test_single(array_shift($langs), 1.0, 'fr', 'ch', 'fr-CH');
        $this->__test_single(array_shift($langs), 0.9, 'fr', null, 'fr;q=0.9');
        $this->__test_single(array_shift($langs), 0.8, 'en', null, 'en;q=0.8');
        $this->__test_single(array_shift($langs), 0.7, 'de', null, 'de;q=0.7');
        $this->__test_single(array_shift($langs), 0.5, '*', null, '*;q=0.5');
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::get_languages_ordered
     */
    public function test__get_languages_ordered()
    {
        //Same string as above but shuffled around a bit
        $parser = new LanguageStringParser('en;q=0.8, fr;q=0.9, de;q=0.7, *;q=0.5, fr-CH');
        $langs = $parser->get_languages_ordered();
        $this->assertCount(5, $langs);

        $this->__test_single(array_shift($langs), 1.0, 'fr', 'ch', 'fr-CH');
        $this->__test_single(array_shift($langs), 0.9, 'fr', null, 'fr;q=0.9');
        $this->__test_single(array_shift($langs), 0.8, 'en', null, 'en;q=0.8');
        $this->__test_single(array_shift($langs), 0.7, 'de', null, 'de;q=0.7');
        $this->__test_single(array_shift($langs), 0.5, '*', null, '*;q=0.5');
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::__construct
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::get_languages
     */
    public function test____construct_with_invalid()
    {
        $parser = new LanguageStringParser('');
        $this->assertCount(0, $parser->get_languages());

        $parser = new LanguageStringParser('fr-CH, fr;b=c');
        $this->assertCount(1, $parser->get_languages());
    }

    private function __test_single(Language $lang, $weight, $language, $variant, $original)
    {
        $this->assertSame($weight, $lang->get_weight());
        $this->assertSame($language, $lang->get_language());
        $this->assertSame($variant, $lang->get_variant());
        $this->assertSame($original, $lang->get_original());
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::_compare_languages_by_weight_only
     */
    public function test___compare_languages_by_weight_only()
    {
        $parser = new LanguageStringParser('');
        $this->assertSame(0, $parser->_compare_languages_by_weight_only(new Language(), new Language()));
        $this->assertSame(0, $parser->_compare_languages_by_weight_only((new Language())->with_specific_weight('q=0.5'), (new Language())->with_specific_weight('q=0.5')));
        $this->assertSame(-1, $parser->_compare_languages_by_weight_only((new Language())->with_specific_weight('q=0.6'), (new Language())->with_specific_weight('q=0.5')));
        $this->assertSame(1, $parser->_compare_languages_by_weight_only((new Language())->with_specific_weight('q=0.5'), (new Language())->with_specific_weight('q=0.6')));
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::_compare_languages_by_wildcard
     */
    public function test___compare_languages_by_wildcard()
    {
        $parser = new LanguageStringParser('');
        $this->assertSame(0, $parser->_compare_languages_by_wildcard(new Language(), new Language()));

        //Same language
        $this->assertSame(0, $parser->_compare_languages_by_wildcard((new Language())->with_language_and_maybe_variant('en'), (new Language())->with_language_and_maybe_variant('en')));

        //Both wildcards
        $this->assertSame(0, $parser->_compare_languages_by_wildcard((new Language())->with_language_and_maybe_variant('*'), (new Language())->with_language_and_maybe_variant('*')));

        //Specific and wildcard
        $this->assertSame(-1, $parser->_compare_languages_by_wildcard((new Language())->with_language_and_maybe_variant('en'), (new Language())->with_language_and_maybe_variant('*')));

        //Wildcard and specific
        $this->assertSame(1, $parser->_compare_languages_by_wildcard((new Language())->with_language_and_maybe_variant('*'), (new Language())->with_language_and_maybe_variant('en')));
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::_compare_languages_by_variant
     */
    public function test___compare_languages_by_variant()
    {
        $parser = new LanguageStringParser('');
        $this->assertSame(0, $parser->_compare_languages_by_variant(new Language(), new Language()));

        //No variants
        $this->assertSame(0, $parser->_compare_languages_by_variant((new Language())->with_language_and_maybe_variant('en'), (new Language())->with_language_and_maybe_variant('en')));

        //Both have same variants
        $this->assertSame(0, $parser->_compare_languages_by_variant((new Language())->with_language_and_maybe_variant('en-US'), (new Language())->with_language_and_maybe_variant('en-US')));

        //Both have variants, but are different
        $this->assertSame(0, $parser->_compare_languages_by_variant((new Language())->with_language_and_maybe_variant('en-US'), (new Language())->with_language_and_maybe_variant('en-GB')));

        //Only one has a variant
        $this->assertSame(-1, $parser->_compare_languages_by_variant((new Language())->with_language_and_maybe_variant('en-US'), (new Language())->with_language_and_maybe_variant('en')));
        $this->assertSame(1, $parser->_compare_languages_by_variant((new Language())->with_language_and_maybe_variant('en'), (new Language())->with_language_and_maybe_variant('en-US')));
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\LanguageStringParser::_compare_languages
     * @dataProvider provider_for_test___compare_languages
     */
    public function test___compare_languages(int $result, string $a, string $b)
    {
        $parser = new LanguageStringParser('');
        $this->assertSame(
                            $result,
                            $parser->_compare_languages(
                                                            (new Language())->with_string($a),
                                                            (new Language())->with_string($b)
                                                    )
                    );
    }

    public function provider_for_test___compare_languages()
    {
        return [
                    [ 1,  'en',     'en-US' ],
                    [ -1, 'en-US',  'en' ],
                    [ -1, 'en-US',  '*' ],
                    [ 1,  '*',      'en-US' ],

                    //Priorities always win
                    [ -1, '*;q=1.0', 'en;q=0.9' ],

                    //Absence of priority implies 1
                    [ 1, '*;q=1.0', 'en' ],

                    //Same priority and both have variants so same
                    [ 0, 'en-GB;q=1.0', 'en-US' ],
        ];
    }
}
