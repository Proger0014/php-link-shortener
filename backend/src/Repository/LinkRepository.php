<?php

namespace App\Repository;

use App\Entity\Link;
use App\Util\LinkUtil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Link>
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private LoggerInterface $logger)
    {
        parent::__construct($registry, Link::class);
    }

    public function save(Link $link): void
    {
        $this->logger->debug("Процесс сохрания Link");

        $em = $this->getEntityManager();

        $this->logger->debug("Генерация короткого кода для ссылки");

        $code = LinkUtil::generateShortCode(Link::URL_SHORT_CODE_LENGTH);

        $this->logger->debug("Генерация короткого кода прошла успешна");

        $em->wrapInTransaction(function (EntityManagerInterface $em) use ($link, $code) {
            $link->shortCode = $code;

            $em->persist($link);
            $em->flush();
        });
        $em->detach($link);
        $this->logger->debug("Link[id={$link->id}, shortCode={$link->shortCode}] был успешно сохранен");
    }
}
