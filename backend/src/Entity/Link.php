<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * TODO: пофиксить ошибку
 * An exception occurred while executing a query: SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'urlTarget' at row 1
 */
#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[ORM\Table(name: 'links')]
class Link
{
    const int URL_TARGET_LENGTH = 600;
    const int URL_SHORT_CODE_LENGTH = 20;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(name: 'urlTarget')]
    public ?string $urlTarget = null;

    #[ORM\Column(name: 'shortCode')]
    public ?string $shortCode = null;
}
