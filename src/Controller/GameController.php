<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\GameRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game_index', methods: ['GET'])]
    public function index(Request $request, GameRepository $gameRepository): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        $games = $q !== ''
            ? $gameRepository->searchByQuery($q)
            : $gameRepository->findAll();

        return $this->render('game/index.html.twig', [
            'games' => $games,
            'q' => $q,
        ]);
    }

    #[Route('/game/{id}', name: 'app_game_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(
        int $id,
        Request $request,
        GameRepository $gameRepository,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $game = $gameRepository->find($id);

        if (!$game) {
            throw $this->createNotFoundException('Jeu introuvable');
        }

        // ---- Liste des avis (affichage) ----
        $reviews = $reviewRepository->findBy(
            ['game' => $game],
            ['createdAt' => 'DESC']
        );

        // ---- Formulaire avis (ajout) ----
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user) {
                $this->addFlash('error', 'Connecte-toi pour laisser un avis.');
                return $this->redirectToRoute('app_login');
            }

            $review->setUser($user);
            $review->setGame($game);
            $review->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Avis ajouté ✅');
            return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
        }

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'reviews' => $reviews,
            'reviewForm' => $form->createView(),
        ]);
    }
}