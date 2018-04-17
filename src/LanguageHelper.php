<?php

declare(strict_types=1);

namespace Vendi\HttpLanguageHelper;

use Vendi\Shared\utils;

final class LanguageHelper
{
    private const KNOWN_BAD_KEY = '!!EMPTY!!';

    public const DEFAULT_KEY_VALUE = 'lang';

    public const KEY_FOR_POST = 'post';

    public const KEY_FOR_GET = 'get';

    public const KEY_FOR_COOKIES = 'cookie';

    public const KEY_FOR_SERVER = 'server';

    public const DEFAULT_KEY_VALUE_FOR_GET = self::DEFAULT_KEY_VALUE;

    public const DEFAULT_KEY_VALUE_FOR_POST = self::DEFAULT_KEY_VALUE;

    public const DEFAULT_KEY_VALUE_FOR_COOKIE = self::DEFAULT_KEY_VALUE;

    public const DEFAULT_KEY_VALUE_FOR_SERVER = 'HTTP_ACCEPT_LANGUAGE';

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

    public static function get_default_keys() : array
    {
        return [
                    self::KEY_FOR_COOKIES => [ self::DEFAULT_KEY_VALUE_FOR_COOKIE ],
                    self::KEY_FOR_GET     => [ self::DEFAULT_KEY_VALUE_FOR_GET ],
                    self::KEY_FOR_POST    => [ self::DEFAULT_KEY_VALUE_FOR_POST ],
                    self::KEY_FOR_SERVER  => [ self::DEFAULT_KEY_VALUE_FOR_SERVER ],
            ];
    }

    public static function detect_client_preference(string $default_server_language, array $all_server_languages, array $keys = null)
    {
        if (! $keys) {
            $keys = self::get_default_keys();
        }

        $langs = [];

        //Search POST first
        if (utils::is_post() && array_key_exists(self::KEY_FOR_POST, $keys)) {
            foreach ($keys[self::KEY_FOR_POST] as $key) {
                $langs[] = self::get_server_language_from_client_string(self::KNOWN_BAD_KEY, $all_server_languages, utils::get_post_value($key));
            }
        }

        //Search GET second
        if (array_key_exists(self::KEY_FOR_GET, $keys)) {
            foreach ($keys[self::KEY_FOR_GET] as $key) {
                $langs[] = self::get_server_language_from_client_string(self::KNOWN_BAD_KEY, $all_server_languages, utils::get_get_value($key));
            }
        }

        //Search cookies third
        if (array_key_exists(self::KEY_FOR_COOKIES, $keys)) {
            foreach ($keys[self::KEY_FOR_COOKIES] as $key) {
                $langs[] = self::get_server_language_from_client_string(self::KNOWN_BAD_KEY, $all_server_languages, utils::get_cookie_value($key));
            }
        }

        //Search server vars last
        if (array_key_exists(self::KEY_FOR_SERVER, $keys)) {
            foreach ($keys[self::KEY_FOR_SERVER] as $key) {
                $langs[] = self::get_server_language_from_client_string(self::KNOWN_BAD_KEY, $all_server_languages, utils::get_server_value($key));
            }
        }

        //Remove known bad keys (which happen when a key search above can't find anything)
        $langs = array_filter(
                                $langs,
                                function ($lang) {
                                    return $lang !== self::KNOWN_BAD_KEY;
                                }
                            );

        //If we don't have anything return the default
        if (0 === count($langs)) {
            return $default_server_language;
        }

        //Return the first item found in the array
        return reset($langs);
    }
}
