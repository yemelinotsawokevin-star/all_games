<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/game')]
final class AdminGameController extends AbstractController
{
    #[Route(name: 'app_admin_game_index', methods: ['GET'])]
    public function index(Request $request, GameRepository $gameRepository): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        $games = $q !== ''
            ? $gameRepository->searchByQuery($q)
            : $gameRepository->findAll();

        return $this->render('admin_game/index.html.twig', [
            'games' => $games,
            'q' => $q,
        ]);
    }

    #[Route('/new', name: 'app_admin_game_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), \PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('games_images_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur upload image.');
                    return $this->redirectToRoute('app_admin_game_new');
                }

                $game->setImage($newFilename);
            }

            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash('success', 'Jeu créé avec succès ✅');
            return $this->redirectToRoute('app_admin_game_index');
        }

        return $this->render('admin_game/new.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_game_show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        return $this->render('admin_game/show.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_game_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Game $game,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), \PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('games_images_dir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur upload image.');
                    return $this->redirectToRoute('app_admin_game_edit', ['id' => $game->getId()]);
                }

                $game->setImage($newFilename);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Jeu modifié ✅');
            return $this->redirectToRoute('app_admin_game_index');
        }

        return $this->render('admin_game/edit.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_game_delete', methods: ['POST'])]
    public function delete(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($game);
            $entityManager->flush();
            $this->addFlash('success', 'Jeu supprimé ✅');
        }

        return $this->redirectToRoute('app_admin_game_index');
    }
}