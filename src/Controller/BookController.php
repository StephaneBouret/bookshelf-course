<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('/{slug}', name: 'book_category')]
    public function category($slug, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$category) {
            // throw new NotFoundHttpException("La catégorie demandée n'existe pas");
            // Deuxième méthode venant de l'AbstractController :
            throw $this->createNotFoundException("La catégorie demandée n'existe pas");
        }

        return $this->render('book/category.html.twig', [
            'slug' => $slug,
            'category' => $category
        ]);
    }
    
    #[Route('/{category_slug}/{slug}', name: 'book_show')]
    public function show($slug, BookRepository $bookRepository)
    {
        $book = $bookRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$book) {
            throw $this->createNotFoundException("Le livre demandé n'existe pas");
        }

        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }
}
