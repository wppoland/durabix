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

    /** Longest brand or model identifier kept, so a compressed line stays legible. */
    public const MAX_CHARS = 40;

    /** Brand starts at x 6.32 and the model ends at x 262.97 on the same line; 8 units stay between them. */
    private const ROW_WIDTH = 248.65;

    public static function full(string $dir, int $years, string $brand, string $model): string
    {
        $brand = mb_substr($brand, 0, self::MAX_CHARS);
        $model = mb_substr($model, 0, self::MAX_CHARS);
        [$brandFit, $modelFit] = self::fit($brand, $model);

        return self::fill($dir . '/assets/garan/label.svg', [
            '{{YEARS}}' => (string) $years,
            '{{BRAND}}' => $brand,
            '{{MODEL}}' => $model,
        ], [
            '{{BRAND_FIT}}' => $brandFit,
            '{{MODEL_FIT}}' => $modelFit,
        ]);
    }

    /** The nested display, which must open the full label on the first click (Annex II, nested display note 2). */
    public static function nested(string $dir, int $years): string
    {
        return self::fill($dir . '/assets/garan/nested.svg', ['{{YEARS}}' => (string) $years]);
    }

    /**
     * Width of a line of 9px Inter, in label units.
     * shortcut: three glyph widths instead of the font's metrics; measure the woff2 if labels still collide.
     */
    public static function textWidth(string $text): float
    {
        $width = 0.0;
        foreach (mb_str_split($text) as $char) {
            $width += match (true) {
                (bool) preg_match('/[ilIjtf1.,:;\'|!\- ]/u', $char) => 2.6,
                (bool) preg_match('/[MWmw@%]/u', $char) || (bool) preg_match('/\p{Lu}/u', $char) => 6.8,
                default => 5.2,
            };
        }

        return $width;
    }

    /**
     * textLength attributes that squeeze brand and model into the row only when they would collide;
     * a shorter line gets none, since textLength would stretch it.
     *
     * @return array{0: string, 1: string}
     */
    public static function fit(string $brand, string $model): array
    {
        $b = self::textWidth($brand);
        $m = self::textWidth($model);
        if ($b + $m <= self::ROW_WIDTH) {
            return ['', ''];
        }

        $half = self::ROW_WIDTH / 2;
        $bMax = $m <= $half ? self::ROW_WIDTH - $m : ($b <= $half ? $b : $half);
        $mMax = self::ROW_WIDTH - min($b, $bMax);

        $attr = static fn (float $w, float $max): string => $w > $max
            ? sprintf(' textLength="%.2F" lengthAdjust="spacingAndGlyphs"', $max)
            : '';

        return [$attr($b, $bMax), $attr($m, $mMax)];
    }

    /**
     * @param array<string, string> $fields Text values, escaped here.
     * @param array<string, string> $raw    Attribute fragments built by this class.
     */
    private static function fill(string $file, array $fields, array $raw = []): string
    {
        $svg = is_readable($file) ? (string) file_get_contents($file) : '';

        return strtr($svg, array_map(static fn (string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $fields) + $raw);
    }
}
