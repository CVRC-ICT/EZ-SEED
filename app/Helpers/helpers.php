<?php

if (! function_exists('clean_place_name')) {

    function clean_place_name(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (mb_check_encoding($value, 'UTF-8') && ! str_contains($value, 'Ã')) {
            return $value;
        }

        $repaired = @mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        $repaired = @iconv('UTF-8', 'UTF-8//IGNORE', $repaired);

        if ($repaired !== false && mb_check_encoding($repaired, 'UTF-8') && ! str_contains($repaired, 'Ã')) {
            return $repaired;
        }

        $fallback = strtr($value, [
            'Ã±' => 'n', 'Ã‘' => 'N', 'ñ' => 'n', 'Ñ' => 'N',
        ]);

        return preg_replace('/[^\x20-\x7E]/', '', $fallback);
    }
}