<?php

declare(strict_types=1);

namespace Durabix\Model;

defined('ABSPATH') || exit;

/**
 * The GARAN label for a producer's commercial guarantee of durability (Annex II to Implementing
 * Regulation (EU) 2025/1960), filled from the Commission's colour SVG. Only the duration, the
 * brand and the model identifier are editable (Annex II, note 1).
 */
final class GaranLabel
{
    /** Where the label's QR code leads (Annex II, note 1(III)). */
    public const INFO_URL = 'https://europa.eu/youreurope/commercial-guarantee-durability/index.htm';

    public static function full(string $dir, int $years, string $brand, string $model): string
    {
        return self::fill($dir . '/assets/garan/label.svg', [
            '{{YEARS}}' => (string) $years,
            '{{BRAND}}' => $brand,
            '{{MODEL}}' => $model,
        ]);
    }

    /** The nested display, which must open the full label on the first click (Annex II, nested display note 2). */
    public static function nested(string $dir, int $years): string
    {
        return self::fill($dir . '/assets/garan/nested.svg', ['{{YEARS}}' => (string) $years]);
    }

    /**
     * @param array<string, string> $fields
     */
    private static function fill(string $file, array $fields): string
    {
        $svg = is_readable($file) ? (string) file_get_contents($file) : '';

        return strtr($svg, array_map(static fn (string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $fields));
    }
}
