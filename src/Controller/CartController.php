<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Form\CartConfirmationType;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    protected $bookRepository;
    protected $cartService;

    public function __construct(BookRepository $bookRepository, CartService $cartService)
    {
        $this->bookRepository = $bookRepository;
        $this->cartService = $cartService;
    }

    #[Route('/cart/add/{id}', name: 'cart_add', requirements: ["id" => '\d+'])]
    public function add($id): Response
    {
        // 0. Sécurisation : est-ce que le livre existe
        $book = $this->bookRepository->find($id);
        if (!$book) {
            throw $this->createNotFoundException("Le livre $id n'existe pas");
        }

        // Ajouter le livre et vérifier si c'est un nouvel emprunt
        $isNewBorrowing = $this->cartService->add($id);

        // Conditionner l'addFlash en fonction de si c'est un nouvel emprunt ou non
        if ($isNewBorrowing) {
            $this->addFlash('success', "Le livre a bien été ajouté au panier");
        } else {
            $this->addFlash('warning', "Le livre est déjà dans le panier");
        }

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/cart', name: 'cart_show')]
    public function show(): Response
    {
        $form = $this->createForm(CartConfirmationType::class);
        $total = $this->cartService->getTotal();
        $detailedCart = $this->cartService->getDetailedCartItems();

        return $this->render('cart/index.html.twig', [
            'items' => $detailedCart,
            'total' => $total,
            'confirmationForm' => $form->createView()
        ]);
    }

    #[Route('/cart/delete/{id}', name: 'cart_delete', requirements: ["id" => '\d+'])]
    public function delete($id): Response
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            throw $this->createNotFoundException("Le livre $id n'existe pas et ne peut pas être supprimé !");
        }

        $this->cartService->remove($id);

        $this->addFlash('success', 'Le livre a bien été supprimé du panier');

        return $this->redirectToRoute('cart_show');
    }
}
