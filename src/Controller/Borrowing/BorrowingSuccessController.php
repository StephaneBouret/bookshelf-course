<?php

namespace App\Controller\Borrowing;

use App\Cart\CartService;
use App\Repository\BorrowingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class BorrowingSuccessController extends AbstractController
{
    protected $em;
    protected $cartService;

    public function __construct(EntityManagerInterface $em, CartService $cartService)
    {
        $this->em = $em;
        $this->cartService = $cartService;
    }

    #[Route('/borrowing/terminate/{id}', 'borrowing_success')]
    public function success($id, BorrowingRepository $borrowingRepository)
    {
        // 1. Je récupère l'emprunt
        $borrowing = $borrowingRepository->find($id);

        if (
            !$borrowing ||
            ($borrowing && $borrowing->getUser() !== $this->getUser())
        ) {
            $this->addFlash("warning", "L'emprunt n'existe pas !");
            return $this->redirectToRoute('borrowing_index');
        }
        // 2. Je fais passer le livre en statut indisponible
        foreach ($borrowing->getBooks() as $book) {
            $book->setIsAvailable(false); // Mettre le livre indisponible
        }
        $this->em->flush();
        // 3. Je vide le panier
        $this->cartService->empty();
        // 4. Je redirige avec un flash vers la liste des emprunts
        $this->addFlash("success", "L'emprunt a été confirmé avec succès !");
        return $this->redirectToRoute('borrowing_index');
    }
}
