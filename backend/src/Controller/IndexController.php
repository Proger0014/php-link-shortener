<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\LinkAggregate;
use App\Entity\Link;
use App\Entity\LinkForm;
use App\Integration\FaviconExtractor;
use App\Repository\LinkRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/web/index', name: 'base_')]
class IndexController extends AbstractController
{
    const string CURRENT_LINK = 'current_link';
    const string LINKS = 'links';
    const int LIMIT_LINKS = 50;

    function __construct(
        private readonly LinkRepository $linkRepository,
        private readonly FaviconExtractor $faviconExtractor,
        private readonly LoggerInterface $logger
    ) { }

    #[Route('', name: 'index_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(LinkForm::class, new Link(), [
            'method' => 'POST',
            'action' => $this->generateUrl('base_index_post')
        ]);

        $session = $request->getSession();
        $currentLinkId = $session->get(self::CURRENT_LINK) ?: 0;
        $currentLink = $this->linkRepository->find($currentLinkId);
        $links = $this->getLinks($session);
        $linksEnriched = $this->linksEnrich($links);

        return $this->render('index/index.html.twig', [
            'form' => $form,
            'currentLink' => $currentLink,
            'links' => $linksEnriched
        ]);
    }

    #[Route('', name: 'index_post', methods: ['POST'])]
    public function createLink(Request $request): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkForm::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->linkRepository->save($link);

            $session = $request->getSession();
            $session->set(self::CURRENT_LINK, $link->id);
            $this->putLink($session, $link);
        } else {
            $this->addFlash('danger', 'Не удалось сократить ссылку');
        }

        return $this->redirectToRoute('base_index_get');
    }

    #[Route('/r/{code}', name: 'redirect', methods: ['GET'])]
    public function handleRedirect(string $code)
    {
        $existsLink = $this->linkRepository->findOneBy([
            'shortCode' => $code,
        ]);

        if (!$existsLink) {
            $this->addFlash('danger', 'Не удалось найти ссылку на ' . $code);
            return $this->redirectToRoute('base_index_get');
        }

        return $this->redirect($existsLink->urlTarget);
    }

    private function putLink(SessionInterface $session, Link $link): void
    {
        $this->logger->debug("Добавление в сессию Links[id={$link->id}]");

        $existsLinks = $session->get(self::LINKS) ?: [];

        if (count($existsLinks) >= self::LIMIT_LINKS) {
            $this->logger->debug("Кол-во элементов в сессии превышает {limit}, последний элемент будет удален");
            array_pop($existsLinks);
        }

        array_unshift($existsLinks, $link->id);

        $session->set(self::LINKS, $existsLinks);
    }

    /**
     * @param SessionInterface $session
     *
     * @return list<Link>
     */
    private function getLinks(SessionInterface $session): array
    {
        $linksId = $session->get(self::LINKS) ?: [];

        $this->logger->debug("Изьято Link из сессии в кол-ве {count}", [
            'count' => count($linksId)
        ]);

        $qb = $this->linkRepository->createQueryBuilder('l');

        $counter = 0;
        foreach ($linksId as $linkId) {
            $qb
                ->orWhere('l.id = :linkId' . $counter)
                ->setParameter('linkId' . $counter, $linkId);
            $counter++;
        }

        return $qb
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<Link> $source
     *
     * @return list<LinkAggregate>
     */
    private function linksEnrich(array $source): array
    {
        $this->logger->debug("Процесс обогащения ссылок");

        return array_map(function (Link $item) {
            $icon = $this->faviconExtractor->extract($item->urlTarget);

            return new LinkAggregate(
                link: $item,
                icon: $icon
            );
        }, $source);
    }
}
