<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper;

final class LanguageStringParser
{
    private $languages = [];

    public function get_languages() : array
    {
        return $this->languages;
    }

    private function __construct()
    {
        //NOOP
    }

    public static function create_empty_parser() : self
    {
        return new self();
    }

    public static function create_from_string(string $languages) : self
    {
        $parts = explode(',', $languages);

        return self::create_from_array($parts);
    }

    public static function create_from_array(array $languages) : self
    {
        $obj = new self();

        foreach ($languages as $part) {
            $string = trim($part);
            if ($string) {
                $lang = (new Language)->with_string($string);
                if (!$lang->get_last_error()) {
                    $obj->languages[] = $lang;
                }
            }
        }

        return $obj;
    }

    public function get_languages_ordered() : array
    {
        $ret = $this->get_languages();
        usort($ret, [ $this, '_compare_languages' ]);
        return $ret;
    }

    public function _compare_languages(Language $a, Language $b) : int
    {
        //Priorities always win unless they are the same
        $weight_result = $this->_compare_languages_by_weight_only($a, $b);
        if (0 !== $weight_result) {
            return $weight_result;
        }

        //Wildcards are the least-specific. If they exist they should
        //always be sorted lowest.
        $wildcard_result = $this->_compare_languages_by_wildcard($a, $b);
        if (0 !== $wildcard_result) {
            return $wildcard_result;
        }

        //Lastly, look at variants. The presence of means that they
        //are more specific.
        $variant_result = $this->_compare_languages_by_variant($a, $b);
        if (0 !== $variant_result) {
            return $variant_result;
        }

        //As far as we can tell they are the same
        return 0;
    }

    public function _compare_languages_by_weight_only(Language $a, Language $b) : int
    {
        //Reverse directions because higher numbers sort lower
        return $b->get_weight() <=> $a->get_weight();
    }

    public function _compare_languages_by_wildcard(Language $a, Language $b) : int
    {
        //I know this can be simplified but I prefer to break them out.

        //Both wildcard. Weird. Whatever.
        if ('*' === $a->get_language() && '*' === $b->get_language()) {
            return 0;
        }

        //If A is a wildcard then B is more specific and should be preferred
        if ('*' === $a->get_language()) {
            return 1;
        }

        //If B is a wildcard then A is more specific and should be preferred
        if ('*' === $b->get_language()) {
            return -1;
        }

        //Both have languages and we can't tell further, same
        return 0;
    }

    public function _compare_languages_by_variant(Language $a, Language $b) : int
    {
        //Neither has a variant, same.
        //en and en
        if (!$a->get_variant() && !$b->get_variant()) {
            return 0;
        }

        //A has a variant but B doesn't, A wins
        //en-US and en
        if ($a->get_variant() && !$b->get_variant()) {
            return -1;
        }

        //B has a variant but A doesn't, B wins
        //en and en-US
        if (!$a->get_variant() && $b->get_variant()) {
            return 1;
        }

        //String comparison isn't done on variants unless someone has local-only
        //rules. For this, if they both have a variant then regardless of the
        //value they are considered the same. So en-US and en-GB are weighted
        //the same.
        return 0;
    }
}
