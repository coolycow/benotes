<?php

namespace App\Services;

use ColorThief\ColorThief;

readonly class ColorService
{
    /**
     * @param string $base_url
     * @return string|null
     */
    public function getDominantColor(string $base_url): ?string
    {
        if (!extension_loaded('gd') & !extension_loaded('imagick') & !extension_loaded('gmagick')) {
            return null;
        }

        $host = parse_url($base_url)['host'];

        try {
            $rgb = ColorThief::getColor('https://www.google.com/s2/favicons?domain=' . $host);
        } catch (\RuntimeException $e) {
            return '#FFF';
        }

        return sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
    }
}
