<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper\UnitTests;

use PHPUnit\Framework\TestCase;
use Vendi\HttpLanguageHelper\Language;

/**
 * @covers \Vendi\HttpLanguageHelper\Language
 */
class test_Language extends TestCase
{
    /**
     * @covers \Vendi\HttpLanguageHelper\Language::with_string
     */
    public function test____construct__empty()
    {
        $v1 = new Language();
        $v2 = $v1->with_string('');

        //Test immutability
        $this->assertNotSame($v1, $v2);
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\Language::add_parsing_error
     * @covers \Vendi\HttpLanguageHelper\Language::get_errors
     * @covers \Vendi\HttpLanguageHelper\Language::get_last_error
     */
    public function test__add_parsing_error()
    {
        $obj = new Language();
        $this->assertNull($obj->get_last_error());
        $this->assertNull($obj->get_errors());

        $obj->add_parsing_error('Cheese');

        $this->assertSame('Cheese', $obj->get_last_error());

        $errors = $obj->get_errors();

        $this->assertInternalType('array', $errors);
        $this->assertCount(1, $errors);
        $this->assertSame('Cheese', reset($errors));
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\Language::with_specific_weight
     */
    public function test__with_specific_weight()
    {
        $obj = new Language();
        $this->assertNull($obj->get_last_error());
        $this->assertNull($obj->get_errors());

        $this->assertSame('Empty weight string', $obj->with_specific_weight('')->get_last_error());
        $this->assertSame('Unknown weight string: a=b=c', $obj->with_specific_weight('a=b=c')->get_last_error());
        $this->assertSame('Missing q in weight string', $obj->with_specific_weight('e=0.5')->get_last_error());
        $this->assertSame('Invalid weight portion: b', $obj->with_specific_weight('q=b')->get_last_error());

        $this->assertSame(1.0, $obj->get_weight());
        $valid = $obj->with_specific_weight('q=0.5');
        $this->assertNull($valid->get_last_error());
        $this->assertSame(0.5, $valid->get_weight());
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\Language::with_language_and_maybe_variant
     */
    public function test__with_language_and_maybe_variant()
    {
        $obj = new Language();
        $this->assertNull($obj->get_last_error());
        $this->assertNull($obj->get_errors());

        $this->assertSame('Null string', $obj->with_language_and_maybe_variant('')->get_last_error());
        $this->assertSame('xyz', $obj->with_language_and_maybe_variant('XYZ')->get_language());
        $this->assertSame('xyz', $obj->with_language_and_maybe_variant('XYZ-PYT')->get_language());
        $this->assertSame('pyt', $obj->with_language_and_maybe_variant('XYZ-PYT')->get_variant());
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\Language::with_variant
     */
    public function test__with_variant()
    {
        $obj = new Language();
        $this->assertNull($obj->get_last_error());
        $this->assertNull($obj->get_errors());

        $this->assertSame('xyz', $obj->with_variant('XYZ')->get_variant());
        $this->assertSame('', $obj->with_variant('')->get_variant());
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\Language::with_string
     */
    public function test__with_string()
    {
        $obj = new Language();
        $this->assertNull($obj->get_last_error());
        $this->assertNull($obj->get_errors());

        $this->assertSame('Null string', $obj->with_string('')->get_last_error());
        $this->assertSame('Weight parser failed', $obj->with_string('en;q=b')->get_last_error());
        $this->assertSame('String parser failed', $obj->with_string(';q=0.5')->get_last_error());

        $valid = $obj->with_string('en-US;q=0.5');
        $this->assertNull($valid->get_last_error());

        $this->assertSame(0.5, $valid->get_weight());
        $this->assertSame('en', $valid->get_language());
        $this->assertSame('us', $valid->get_variant());
        $this->assertSame('en-US;q=0.5', $valid->get_original());
    }

    /**
     * @covers \Vendi\HttpLanguageHelper\Language::get_variant
     * @covers \Vendi\HttpLanguageHelper\Language::get_language
     * @covers \Vendi\HttpLanguageHelper\Language::get_weight
     * @covers \Vendi\HttpLanguageHelper\Language::get_original
     */
    public function test__properties()
    {
        $obj = (new Language())
                ->with_string('en-US;q=0.5')
        ;

        $this->assertSame(0.5, $obj->get_weight());
        $this->assertSame('en', $obj->get_language());
        $this->assertSame('us', $obj->get_variant());
        $this->assertSame('en-US;q=0.5', $obj->get_original());

        $obj = (new Language())
                ->with_string('')
        ;

        //This one usually can never be null however it will be if there's a
        //parsing error
        $this->assertNull($obj->get_weight());
        $this->assertNull($obj->get_language());
        $this->assertNull($obj->get_variant());
    }
}
