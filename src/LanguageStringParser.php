<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageParser;

final class LanguageStringParser
{
    private $languages = [];

    public function get_languages() : array
    {
        return $this->languages;
    }

    public function __construct(string $string)
    {
        $parts = explode(',', $string);
        foreach ($parts as $part) {
            $string = trim($part);
            if($string){
                $this->languages[] = (new Language)->with_string($string);
            }
        }
    }
}
