<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchFormType;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class BookController extends AbstractController
{
    #[Route('/{slug}', name: 'book_category', priority: -1)]
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
    
    #[Route('/{category_slug}/{slug}', name: 'book_show', priority: -1)]
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

    #[Route('/books', name: 'book_display')]
    public function display(Request $request, BookRepository $bookRepository): Response
    {
        $data = new SearchData;
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchFormType::class, $data);
        $form->handleRequest($request);

        [$minDate, $maxDate] = $bookRepository->findMinMaxDate($data);
        $books = $bookRepository->findSearch($data);

        return $this->render('book/display.html.twig', [
            'books' => $books,
            'form' => $form,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
        ]);
    }
}
