<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper;

final class LanguageHelper
{
    public static function get_server_language_from_client_string(string $default_server_language, array $all_server_languages, string $client_string) : string
    {
        $client_languages = LanguageStringParser::create_from_string($client_string)->get_languages_ordered();
        if (0 === count($client_languages)) {
            //No client languages, return server default
            return $default_server_language;
        }

        $server_languages = LanguageStringParser::create_from_array($all_server_languages)->get_languages_ordered();
        if (0 === count($server_languages)) {
            //No server languages, return server default
            return $default_server_language;
        }

        //We've got at least one valid server language and one valid client language

        foreach ($client_languages as $client_lang) {

            //Look for exact matches first
            foreach ($server_languages as $server_lang) {
                if ($client_lang->get_language() === $server_lang->get_language() && $client_lang->get_variant() === $server_lang->get_variant()) {
                    return $server_lang->get_original();
                }
            }

            //Look for language-only matches next
            foreach ($server_languages as $server_lang) {
                if ($client_lang->get_language() === $server_lang->get_language()) {
                    return $server_lang->get_original();
                }
            }
        }

        return $default_server_language;
    }
}
