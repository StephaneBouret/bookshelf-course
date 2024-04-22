<?php

namespace App\Controller\Borrowing;

use App\Repository\BorrowingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BorrowingExtendController extends AbstractController
{
    #[Route('/borrowing/extend/{id}', name: 'borrowing_extend')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour demander une extension')]
    public function extend($id, BorrowingRepository $borrowingRepository, EntityManagerInterface $em): Response
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

        // 2. Ajouter 6 jours de plus à la date du jour afin de modifier la date de restitution
        $newDueDate = (new \DateTimeImmutable())->modify('+6 days');
        $borrowing->setDueDateAt($newDueDate);

        $em->flush();
        $this->addFlash("success", "La date de restitution a été mis à jour");
        return $this->redirectToRoute('borrowing_index');
    }
}
