<?php

final class Language
{
    public $language;

    public $variant;

    public $weight;

    public $original;

    public $is_valid = false;

    private $parsing_errors = null;

    public function __construct(string $string = null)
    {
        if($string)
        {
            $this->set_by_string($string);
        }
    }

    public function set_by_string(string $string) : self
    {
        $this->original = $string;

        if(!$string)
        {
            $this->add_parsing_error('Null string');
            return $this;
        }

        $working_string = $string;

        $parts = explode(';', $string);
        if(2 === count($parts))
        {
            if(!$this->maybe_set_weight($parts[1]))
            {
                $this->add_parsing_error('Weight parser failed');
                return $this;
            }
        }
        $working_string = $parts[0];

        $parts = explode('-', $working_string);
        if(2 === count($parts))
        {
            $this->variant = $parts[1];
        }

        $this->language = $parts[0];

        return $this;
    }

    private function maybe_set_weight(string $weight_string) : bool
    {
        if(!$weight_string)
        {
            $this->add_parsing_error('Empty weight string');
            return false;
        }

        $parts = explode('=', $weight_string);
        if(2 !== count($parts))
        {
            $this->add_parsing_error('Unknown weight string: ' . $weight_string);
            return false;
        }

        if('q' !== $parts[0])
        {
            $this->add_parsing_error('Missing q in weight string');
            return false;
        }

        $weight = $parts[1];
        if(!preg_match('/[0-9\.]/', $weight))
        {
            $this->add_parsing_error('Invalid weight portion: ' . $weight);
            return false;
        }

        $this->weight = floatval($weight);
        return true;
    }

    public function get_last_error() : ?string
    {
        if(is_array($this->parsing_errors) && count($this->parsing_errors) > 0)
        {
            return reset($this->parsing_errors);
        }

        return null;
    }

    public function get_errors() : ?array
    {
        return $this->parsing_errors;
    }

    private function add_parsing_error(string $message)
    {
        if(!is_array($this->parsing_errors))
        {
            $this->parsing_errors = [];
        }

        $this->parsing_errors[] = $message;
    }
}
