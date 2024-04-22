<?php

namespace App\Borrowing;

use App\Cart\CartService;
use App\Entity\Borrowing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class BorrowingPersister
{
    protected $security;
    protected $cartService;
    protected $em;

    public function __construct(Security $security, CartService $cartService, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->cartService = $cartService;
        $this->em = $em;
    }
    public function storeBorrowing(Borrowing $borrowing)
    {
        // intégrer tout ce qu'il faut et persister l'emprunt
        // 6. Nous allons le lier à l'utilisateur actuellement connecté - Security
        $borrowing->setUser($this->security->getUser());
        // 7. Nous allons le lier avec les livres qui sont dans le panier - CartService
        $newDueDate = (new \DateTimeImmutable())->modify('+6 days');
        foreach ($this->cartService->getDetailedCartItems() as $cartItem) {
            // Supposons que $cartItem soit une instance de la classe CartItem
            $book = $cartItem->book;
            // Ajouter le livre à l'emprunt
            $borrowing->addBook($book)
                ->setBorrowingAt(new \DateTimeImmutable())
                ->setTotal($this->cartService->getTotal())
                ->setDueDateAt($newDueDate);
        }
        $this->em->persist($borrowing);
    }
}
