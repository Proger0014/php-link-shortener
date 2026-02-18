<?php

declare(strict_types=1);

namespace App\Tests\Integration\NonTest\Config;

readonly class FaviconExtractorConfig
{
    public function __construct(
        public string $haveFavicon,
        public string $notHaveFavicon,
        public string $notSuccessResponse
    ) { }
}
