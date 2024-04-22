<?php

namespace App\Controller\Borrowing;

use App\Entity\User;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BorrowingsListController extends AbstractController
{
    #[Route('/borrowings', name: 'borrowing_index')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à vos emprunts')]
    public function index(): Response
    {
        // 1. Nous devons nous assurer que la personne est connectée sinon redirection vers la page d'accueil
        // 2. Nous voulons savoir qui est connecté ?
        /** @var User */
        $user = $this->getUser();

        // Obtenir la date actuelle
        $now = new \DateTimeImmutable();

        $borrowings = $user->getBorrowings();
        foreach ($borrowings as $borrowing) {
            if ($borrowing->getDueDateAt() < $now) {
                $borrowing->setIsOverdue(true);
                $this->addFlash('danger', 'Vous êtes en retard pour le retour des livres. Veuillez consulter la liste ci-dessous');
            } else {
                $borrowing->setIsOverdue(false);
            }
        }

        // 3. Nous voulons passer l'utilisateur à twig afin d'afficher ses emprunts
        return $this->render('borrowing/index.html.twig', [
            'borrowings' => $borrowings,
        ]);
    }
}
