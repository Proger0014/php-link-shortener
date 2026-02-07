<?php

namespace App\Util;

final readonly class LinkUtil
{
    public static function generateShortCode(int $maxLength): string
    {
        $length = random_int(1, $maxLength);
        $code = '';
        $start = mb_ord('a');
        $end = mb_ord('z');

        while (strlen($code) < $length) {
            $isNumber = random_int(0, 1);

            if ($isNumber) {
                $code .= random_int(0, 9);
            } else {
                $char = random_int($start, $end);
                $code .= mb_chr($char);
            }
        }

        return $code;
    }
}
