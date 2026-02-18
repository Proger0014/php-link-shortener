<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Link;
use App\Entity\LinkForm;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/web/index', name: 'base_')]
class IndexController extends AbstractController
{
    const string CURRENT_LINK = 'current_link';

    function __construct(
        private readonly LinkRepository $linkRepository
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

        return $this->render('index/index.html.twig', [
            'form' => $form,
            'currentLink' => $currentLink,
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

            $request->getSession()->set(self::CURRENT_LINK, $link->id);
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
}
