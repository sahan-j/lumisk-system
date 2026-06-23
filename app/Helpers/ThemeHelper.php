<?php

namespace App\Helpers;

use App\Models\Company;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ThemeHelper
{
    private static ?array $cache = null;

    private const DEFAULTS = [
        'color_1' => '#00d4ff',
        'color_2' => '#6d5cff',
        'sidebar_bg' => '#0f0f0f',
        'preset' => 'default',
    ];

    public static function getColors(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        // Resilient to the pre-migration deploy window (columns/table may not exist yet).
        try {
            $company = Company::query()->first();
        } catch (Throwable $e) {
            $company = null;
        }

        $colors = [
            'color_1' => $company?->theme_color_1 ?: self::DEFAULTS['color_1'],
            'color_2' => $company?->theme_color_2 ?: self::DEFAULTS['color_2'],
            'sidebar_bg' => $company?->theme_sidebar_bg ?: self::DEFAULTS['sidebar_bg'],
            'preset' => $company?->theme_preset ?: self::DEFAULTS['preset'],
        ];

        $colors['color_1_soft'] = self::hexToRgba($colors['color_1'], 0.12);
        $colors['color_2_soft'] = self::hexToRgba($colors['color_2'], 0.12);
        $colors['color_1_15'] = self::hexToRgba($colors['color_1'], 0.15);
        $colors['color_2_08'] = self::hexToRgba($colors['color_2'], 0.08);
        $colors['gradient'] = "linear-gradient(135deg, {$colors['color_1']}, {$colors['color_2']})";
        $colors['gradient_90'] = "linear-gradient(90deg, {$colors['color_1']}, {$colors['color_2']})";
        $colors['gradient_soft'] = "linear-gradient(135deg, {$colors['color_1_soft']}, {$colors['color_2_soft']})";

        return self::$cache = $colors;
    }

    public static function getCss(): string
    {
        return Cache::remember('lumisk_theme_css', 3600, function () {
            $c = self::getColors();

            return ":root{"
                . "--brand-1:{$c['color_1']};"
                . "--brand-2:{$c['color_2']};"
                . "--brand-gradient:{$c['gradient']};"
                . "--brand-gradient-90:{$c['gradient_90']};"
                . "--brand-gradient-soft:{$c['gradient_soft']};"
                . "--brand-1-soft:{$c['color_1_soft']};"
                . "--brand-2-soft:{$c['color_2_soft']};"
                . "--brand-1-15:{$c['color_1_15']};"
                . "--brand-2-08:{$c['color_2_08']};"
                . "--sidebar-bg:{$c['sidebar_bg']};"
                . "}";
        });
    }

    private static function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return "rgba(109,92,255,{$alpha})";
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r},{$g},{$b},{$alpha})";
    }

    public static function clearCache(): void
    {
        self::$cache = null;
        Cache::forget('lumisk_theme_css');
    }
}
