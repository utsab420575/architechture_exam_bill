<?php

namespace App\Services;

class NumberToWordsHelper
{
    /**
     * Convert numeric amount to words formatted as:
     * e.g. "Thirteen thousand five hundred Taka (Only)"
     *
     * @param float|int|string|null $number
     * @return string
     */
    public static function toWords($number): string
    {
        $ones = [
            0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
            6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
            11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen'
        ];

        $tens = [
            2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty',
            6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'
        ];

        $num = round((float)($number ?? 0), 2);
        if ($num <= 0) {
            return 'Zero Taka (Only)';
        }

        $parts = explode('.', number_format($num, 2, '.', ''));
        $intVal = (int)$parts[0];
        $fraction = (int)$parts[1];

        $convertGroup = function (int $n) use ($ones, $tens): string {
            $res = '';
            if ($n >= 100) {
                $res .= $ones[(int)($n / 100)] . ' hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $res .= $tens[(int)($n / 10)] . ' ';
                $n %= 10;
            }
            if ($n > 0) {
                $res .= $ones[$n] . ' ';
            }
            return trim($res);
        };

        $words = '';

        // Crore (1,00,00,000)
        if ($intVal >= 10000000) {
            $crore = (int)($intVal / 10000000);
            $words .= $convertGroup($crore) . ' crore ';
            $intVal %= 10000000;
        }

        // Lakh (1,00,000)
        if ($intVal >= 100000) {
            $lakh = (int)($intVal / 100000);
            $words .= $convertGroup($lakh) . ' lakh ';
            $intVal %= 100000;
        }

        // Thousand (1,00,000 / 1,000)
        if ($intVal >= 1000) {
            $thousand = (int)($intVal / 1000);
            $words .= $convertGroup($thousand) . ' thousand ';
            $intVal %= 1000;
        }

        // Hundreds and below
        if ($intVal > 0) {
            $words .= $convertGroup($intVal) . ' ';
        }

        $words = ucfirst(trim(preg_replace('/\s+/', ' ', $words)));
        $out = $words ? $words . ' Taka' : '';

        if ($fraction > 0) {
            $fractionWords = $convertGroup($fraction);
            $out .= ($out ? ' and ' : '') . $fractionWords . ' Paisa';
        }

        return $out . ' (Only)';
    }
}
