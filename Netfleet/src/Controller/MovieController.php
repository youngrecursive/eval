<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MovieRepository;
use App\Form\MovieType;
use App\Entity\Movie;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;


class MovieController extends AbstractController
{
    #[Route('/', name: 'app_movie_home')]
    public function index(MovieRepository $movieRepository)
    {
        $movies = $movieRepository->findAll();
        return $this->render('movies/index.html.twig', [
            'movies' => $movies
        ]);
    }

    #[Route('/list', name: 'app_movie_list')]
    public function list(MovieRepository $movieRepository): Response
    {
        $movies = $movieRepository->findAll();
        $formatMovies = [];
        foreach ($movies as $movie) {
            $formatMovies[] = [
                'nom' => $movie->getNom(),
                'type' => $movie->getType(),
                'date_creation' => $movie->getDateCreation(),
                'synospis' => $movie->getSynopsis()
            ];
        }

        if ($movies) {
            return $this->json([
                'data' => $formatMovies,
                'message' => 'La ressource a été récupérée et est retransmise dans le corps du message.'
            ], 200);
        }
        else {
            return $this->json('erreur', 400);
        }
        
    }

    #[Route('/single/{id}', name: 'app_movie_single')]
    public function single(MovieRepository $movieRepository, int $id): Response
    {   
        if ($id) {
            $movie = $movieRepository->find($id);
            $formatMovie = [];
            if ($movie) {
                $formatMovie = [
                    'nom' => $movie->getNom(),
                    'synopsis' => $movie->getSynopsis(),
                    'date_creation' => $movie->getDateCreation(),
                    'type' => $movie->getType()
                ];
                return $this->json([
                    'data' => $formatMovie,
                    'message' => 'La ressource a été récupérée et est retransmise dans le corps du message.'
                ], 200);
            }

            return $this->json('erreur', 400);
        }
        return $this->json('référence introuvable', 404);
    }

    #[Route('/create', name: 'app_movie_create', methods: ['GET', 'POST'])]
    public function create(MovieRepository $movieRepository, Request $request, ManagerRegistry $doctrine)
    {
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            $movie = $form->getData();

            $entityManager->persist($movie);
            $entityManager->flush();

        }

        return $this->render('movies/create.html.twig', [
            'form_movie' => $form,
        ]);
    }
}
