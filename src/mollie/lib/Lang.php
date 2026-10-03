<?php

namespace ScriptVortex\WhmcsMollie;

class Lang
{
    /** WHMCS language name => Mollie locale */
    private const LOCALES = [
        'catalan' => 'ca_ES',
        'czech' => 'cs_CZ',
        'danish' => 'da_DK',
        'dutch' => 'nl_NL',
        'english' => 'en_US',
        'english-uk' => 'en_GB',
        'finnish' => 'fi_FI',
        'french' => 'fr_FR',
        'german' => 'de_DE',
        'hungarian' => 'hu_HU',
        'italian' => 'it_IT',
        'norwegian' => 'nb_NO',
        'polish' => 'pl_PL',
        'portuguese-pt' => 'pt_PT',
        'spanish' => 'es_ES',
        'swedish' => 'sv_SE',
        'turkish' => 'tr_TR',
    ];

    private static array $cache = [];

    public static function language(array $params): string
    {
        $candidates = [
            $params['clientdetails']['language'] ?? null,
            $_SESSION['Language'] ?? null,
            $_SESSION['language'] ?? null,
        ];

        if (class_exists('WHMCS\Config\Setting')) {
            $candidates[] = \WHMCS\Config\Setting::getValue('Language');
        }

        foreach ($candidates as $language) {
            $language = strtolower(trim((string) $language));

            if ($language !== '' && preg_match('/^[a-z_-]+$/', $language)) {
                return $language;
            }
        }

        return 'english';
    }

    /**
     * Returns the strings for the given language, falling back to English per key.
     */
    public static function strings(string $language): array
    {
        if (!isset(self::$cache[$language])) {
            $strings = require __DIR__ . '/../lang/english.php';
            $file = __DIR__ . '/../lang/' . $language . '.php';

            if ($language !== 'english' && preg_match('/^[a-z_-]+$/', $language) && is_file($file)) {
                $strings = array_replace_recursive($strings, require $file);
            }

            self::$cache[$language] = $strings;
        }

        return self::$cache[$language];
    }

    public static function mollieLocale(string $language): ?string
    {
        return self::LOCALES[$language] ?? null;
    }
}
