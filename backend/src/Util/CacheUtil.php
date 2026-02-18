<?php

declare(strict_types=1);

namespace App\Util;

class CacheUtil
{
    public static function createKey(string $key): string
    {
        return hash('md5', $key);
    }
}
