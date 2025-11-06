<?php

if (! function_exists('numberToWords')) {
    /**
     * Convert a number (with 2 decimal places) into English words.
     * Uses NumberFormatter spellout when available and falls back to a simple implementation.
     *
     * Examples:
     *  numberToWords('123.45') => "one hundred twenty-three and 45/100"
     */
    function numberToWords($num)
    {
        // Normalize to string with 2 decimals
        $s = (string) $num;
        if (strpos($s, '.') === false) {
            $s = number_format((float)$s, 2, '.', '');
        }
        list($intPart, $decPart) = explode('.', $s);

        $intVal = (int) $intPart;
        $decVal = (int) substr(str_pad($decPart, 2, '0', STR_PAD_RIGHT), 0, 2);

        // Use intl NumberFormatter if available for a nicer spelling
        if (class_exists('\NumberFormatter')) {
            try {
                $fmt = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                $words = $fmt->format($intVal);
                // Ensure ASCII and lowercase
                $words = trim(strtolower(preg_replace('/\s+/', ' ', $words)));
                if ($decVal > 0) {
                    return $words . ' and ' . sprintf('%02d', $decVal) . '/100';
                }
                return $words;
            } catch (\Throwable $e) {
                // fall back
            }
        }

        // Basic fallback implementation for integers up to 999,999,999
        $units = ['', 'one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];
        $tens = ['', '', 'twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];

        $convert_hundreds = function($n) use (&$units, &$tens, &$convert_hundreds) {
            $n = (int)$n;
            $out = '';
            if ($n >= 100) {
                $out .= $units[intval($n/100)] . ' hundred';
                $n = $n % 100;
                if ($n) $out .= ' ';
            }
            if ($n >= 20) {
                $out .= $tens[intval($n/10)];
                if ($n % 10) $out .= '-' . $units[$n%10];
            } elseif ($n > 0) {
                $out .= $units[$n];
            }
            return $out;
        };

        $parts = [];
        $billions = intval($intVal / 1000000000);
        if ($billions) {
            $parts[] = $convert_hundreds($billions) . ' billion';
            $intVal %= 1000000000;
        }
        $millions = intval($intVal / 1000000);
        if ($millions) {
            $parts[] = $convert_hundreds($millions) . ' million';
            $intVal %= 1000000;
        }
        $thousands = intval($intVal / 1000);
        if ($thousands) {
            $parts[] = $convert_hundreds($thousands) . ' thousand';
            $intVal %= 1000;
        }
        if ($intVal) {
            $parts[] = $convert_hundreds($intVal);
        }

        $words = implode(' ', $parts);
        if ($words === '') $words = 'zero';
        if ($decVal > 0) return $words . ' and ' . sprintf('%02d', $decVal) . '/100';
        return $words;
    }
}

// Ensure format_currency is defined at global scope so views can call it without requiring
// numberToWords() to have been invoked first. This prevents "Call to undefined function format_currency()" errors.
if (! function_exists('format_currency')) {
    /**
     * Format an amount in Ugandan Shillings (UGX) with thousands separators.
     * Defaults to no decimal places (UGX has no minor unit).
     * Usage: format_currency(12345) => "UGX 12,345"
     */
    function format_currency($amount, $symbol = 'UGX', $decimals = 0)
    {
        if ($amount === null || $amount === '') return '';
        // Ensure numeric
        $n = is_numeric($amount) ? $amount : floatval(preg_replace('/[^0-9.-]+/', '', (string)$amount));
        return $symbol . ' ' . number_format((float)$n, (int)$decimals, '.', ',');
    }
}
