<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Link;

readonly class LinkAggregate
{
    function __construct(
        public Link $link,
        public ?string $icon
    ) { }
}
