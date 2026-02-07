<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[ORM\Table(name: 'links')]
class Link
{
    const int URL_TARGET_LENGTH = 600;
    const int URL_SHORT_CODE_LENGTH = 20;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) ?int $id = null;
    private(set) ?string $urlTarget;
    private(set) ?string $shortCode;
}
