<?php

declare(strict_types=1);

namespace Durabix\Model;

defined('ABSPATH') || exit;

/**
 * The harmonised notice on the legal guarantee of conformity (Article 22a(1) of Directive 2011/83/EU,
 * Annex I to Implementing Regulation (EU) 2025/1960), shipped as the Commission's colour files.
 */
final class HarmonisedNotice
{
    /** The 24 official EU languages the Commission publishes the notice in. */
    public const LANGUAGES = ['bg', 'cs', 'da', 'de', 'el', 'en', 'es', 'et', 'fi', 'fr', 'ga', 'hr', 'hu', 'it', 'lt', 'lv', 'mt', 'nl', 'pl', 'pt', 'ro', 'sk', 'sl', 'sv'];

    /**
     * Notice language for a WordPress locale; a language outside the 24 gets the English notice.
     */
    public static function language(string $locale): string
    {
        $language = strtolower(substr($locale, 0, 2));
        // Catalan, Basque and Galician shops sell in Spain: the Spanish notice, not the English one.
        $language = in_array($language, ['ca', 'eu', 'gl'], true) ? 'es' : $language;

        return in_array($language, self::LANGUAGES, true) ? $language : 'en';
    }

    public static function imageFile(string $language): string
    {
        return 'assets/notice/notice-' . self::language($language) . '.png';
    }

    /**
     * The Your Europe page the notice's QR code leads to, in the same language.
     */
    public static function infoUrl(string $language): string
    {
        return 'https://europa.eu/youreurope/citizens/consumers/shopping/guarantees/index_' . self::language($language) . '.htm';
    }
}
