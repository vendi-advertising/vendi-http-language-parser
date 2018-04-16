<?php

final class LanguageStringParser
{
    public $languages = [];

    public function __construct(string $string)
    {
        $parts = explode(',', $string);
        foreach($parts as $part)
        {
            $this->languages[] = new Language($part);
        }
    }
}
