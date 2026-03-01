<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'app_wishlist')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, GameRepository $gameRepository): Response
    {
        $ids = $request->getSession()->get('wishlist', []);
        $games = $ids ? $gameRepository->findBy(['id' => $ids]) : [];

        return $this->render('page/wishlist.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/wishlist/add/{id}', name: 'app_wishlist_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(int $id, Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $ids = $session->get('wishlist', []);

        if (!in_array($id, $ids, true)) {
            $ids[] = $id;
        }

        $session->set('wishlist', $ids);

        $this->addFlash('success', 'Jeu ajouté à la wishlist ✅');

        return $this->redirectToRoute('app_game_show', ['id' => $id]);
    }

    #[Route('/wishlist/remove/{id}', name: 'app_wishlist_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function remove(int $id, Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $ids = $session->get('wishlist', []);
        $ids = array_values(array_filter($ids, fn ($v) => (int) $v !== $id));
        $session->set('wishlist', $ids);

        $this->addFlash('success', 'Jeu retiré de la wishlist ✅');

        return $this->redirectToRoute('app_wishlist');
    }
}