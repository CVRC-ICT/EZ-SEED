<?php

namespace App\Support;

class PlaceName
{

    public static function clean(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $repaired = @mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        $repaired = @iconv('UTF-8', 'UTF-8//IGNORE', $repaired);
        $value = ($repaired !== false && $repaired !== '') ? $repaired : $value;

        $value = strtr($value, [
            "\xC3\x83\xC2\xB1" => 'n', 
            "\xC3\x83\xE2\x80\x98" => 'N', 
            "\xC3\xB1" => 'n',          
            "\xC3\x91" => 'N',          


            "\xEF\xBF\xBD" => 'n',

            "\xC3\xAF\xC2\xBF\xC2\xBD" => 'n',
        ]);


        return preg_replace('/[^\x20-\x7E]/', '', $value);
    }
}