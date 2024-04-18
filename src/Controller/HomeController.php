<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function homepage(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findBy([], [], 6);
        return $this->render('home/index.html.twig', [
            'books' => $books,
        ]);
    }
}
