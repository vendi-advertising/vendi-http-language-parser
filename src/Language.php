<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper;

final class Language
{
    public const VARIANT_SIGNIFIER = '-';

    public const WEIGHT_SIGNIFIER = ';';

    public const WEIGHT_BEGIN = 'q';

    public const WEIGHT_SEPARATOR = '=';

    public const DEFAULT_WEIGHT = 1.0;

    private $language;

    private $variant;

    //Spec says that 1 is the default
    private $weight = self::DEFAULT_WEIGHT;

    private $original;

    private $parsing_errors = null;

    public function get_variant() : ?string
    {
        if ($this->get_last_error()) {
            return null;
        }
        return $this->variant;
    }

    public function get_language() : ?string
    {
        if ($this->get_last_error()) {
            return null;
        }
        return $this->language;
    }

    public function get_weight() : ?float
    {
        //Weight should always be a number, even if absent, however we're
        //return null to be consistent with get_variant() and get_language()
        if ($this->get_last_error()) {
            return null;
        }
        return $this->weight;
    }

    public function get_original() : ?string
    {
        //Always return original, even if there's an error
        return $this->original;
    }

    public function with_string(string $string) : self
    {
        //Immutable mode
        $obj = clone $this;

        //Backup copy
        $obj->original = $string;

        if (!$string) {
            $obj->add_parsing_error('Null string');
            return $obj;
        }

        //Look for a weight
        $parts = explode(self::WEIGHT_SIGNIFIER, $string);
        if (2 === count($parts)) {
            $obj = $obj->with_specific_weight($parts[1]);

            if ($obj->get_last_error()) {
                $obj->add_parsing_error('Weight parser failed');
                return $obj;
            }
        }

        //Even if there was no weight, the first part of the array would hold
        //the entire string anyway so that's what we're working with.
        $obj = $obj->with_language_and_maybe_variant($parts[0]);
        if ($obj->get_last_error()) {
            $obj->add_parsing_error('String parser failed');
            return $obj;
        }

        return $obj;
    }

    public function with_language_and_maybe_variant(string $string) : self
    {
        //Immutable mode
        $obj = clone $this;

        if (!$string) {
            $obj->add_parsing_error('Null string');
            return $obj;
        }

        //Look for a variant signifier
        $parts = explode(self::VARIANT_SIGNIFIER, $string);
        if (2 === count($parts)) {
            $obj = $obj->with_variant($parts[1]);
        }

        $obj->language = mb_strtolower($parts[0]);

        return $obj;
    }

    public function with_variant(string $string) : self
    {
        $obj = clone $this;
        $obj->variant = mb_strtolower($string);
        return $obj;
    }

    public function with_specific_weight(string $weight_string) : self
    {
        $obj = clone $this;

        if (!$weight_string) {
            $obj->add_parsing_error('Empty weight string');
            return $obj;
        }

        $parts = explode(self::WEIGHT_SEPARATOR, $weight_string);
        if (2 !== count($parts)) {
            $obj->add_parsing_error('Unknown weight string: ' . $weight_string);
            return $obj;
        }

        if (self::WEIGHT_BEGIN !== $parts[0]) {
            $obj->add_parsing_error('Missing q in weight string');
            return $obj;
        }

        $weight = $parts[1];
        if (!preg_match('/[0-9\.]/', $weight)) {
            $obj->add_parsing_error('Invalid weight portion: ' . $weight);
            return $obj;
        }

        $obj->weight = floatval($weight);
        return $obj;
    }

    public function get_last_error() : ?string
    {
        if (is_array($this->parsing_errors) && count($this->parsing_errors) > 0) {
            return end($this->parsing_errors);
        }

        return null;
    }

    public function get_errors() : ?array
    {
        return $this->parsing_errors;
    }

    public function add_parsing_error(string $message)
    {
        if (!is_array($this->parsing_errors)) {
            $this->parsing_errors = [];
        }

        $this->parsing_errors[] = $message;
    }
}
