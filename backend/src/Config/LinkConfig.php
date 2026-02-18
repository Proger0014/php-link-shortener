<?php

declare(strict_types=1);

namespace App\Config;

readonly class LinkConfig
{
    function __construct(
        public string $defaultIcon
    ) { }
}
