<?php

namespace App\Controller\Borrowing;

use App\Borrowing\BorrowingPersister;
use App\Cart\CartService;
use App\Entity\Borrowing;
use App\Form\CartConfirmationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BorrowingConfirmationController extends AbstractController
{
    protected $cartService;
    protected $em;
    protected $persister;

    public function __construct(CartService $cartService, EntityManagerInterface $em, BorrowingPersister $persister)
    {
        $this->cartService = $cartService;
        $this->em = $em;
        $this->persister = $persister;
    }

    #[Route('/borrowing/confirm', name: 'borrowing_confirm')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour confirmer une réservation')]
    public function confirm(Request $request)
    {
        // 1. Nous voulons lire les données du formulaire - FormFactoryInterface / Request
        $form = $this->createForm(CartConfirmationType::class);
        $form->handleRequest($request);
        // 2. Si le formulaire n'est pas soumis : redirection
        if (!$form->isSubmitted()) {
            // Message Flash puis redirection
            $this->addFlash('warning', 'Vous devez remplir le formulaire de confirmation');
            return $this->redirectToRoute('cart_show');
        }

        // 4. S'il n'y a pas de produits dans le panier : redirection - CartService
        $cartItems = $this->cartService->getDetailedCartItems();
        if (count($cartItems) === 0) {
            $this->addFlash('warning', 'Vous ne pouvez pas confirmer une réservation avec un panier vide');
            return $this->redirectToRoute('cart_show');
        }
        // 5. Nous allons créer un Emprunt
        /** @var Borrowing */
        $borrowing = $form->getData();

        $this->persister->storeBorrowing($borrowing);

        // 8. Nous allons enregistrer la réservation - EntityManagerInterface
        $this->em->flush();
        // $this->cartService->empty();
        // $this->addFlash('success', 'La réservation a bien été enregistrée');
        return $this->redirectToRoute('borrowing_success', [
            'id' => $borrowing->getId()
        ]);
    }
}
