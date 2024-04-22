<?php

namespace App\Controller\Admin;

use App\Entity\Borrowing;
use App\Form\Type\CustomDateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BorrowingCrudController extends AbstractCrudController
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return Borrowing::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Emprunt')
            ->setPageTitle('new', 'Créer un emprunt')
            ->setPaginatorPageSize(10)
            ->setEntityLabelInSingular('un Emprunt');
    }

    public function configureActions(Actions $actions): Actions
    {
        $returnDateBorrowing  = Action::NEW('returnDate', 'Enregistrement de la date de restitution', 'fas fa-reply')->linkToCrudAction('returnDate');
        $actions = parent::configureActions($actions);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, $returnDateBorrowing)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateField::new('borrowingAt', 'Date d\'emprunt du livre')
                ->setFormType(CustomDateType::class)
                ->renderAsChoice(),
            DateField::new('dueDateAt', 'Date de restitution prévue')
                ->setFormType(CustomDateType::class)
                ->renderAsChoice(),
            DateField::new('returnDateAt', 'Date de restitution')->onlyOnIndex(),
            AssociationField::new('user', 'Utilisateur'),
            CollectionField::new('books', 'Livres'),
        ];
    }

    public function returnDate(AdminContext $context): Response
    {
        $borrow = $context->getEntity()->getInstance();

        // Récupérer les livres empruntés
        $books = $borrow->getBooks();
        // Parcourir les livres empruntés et mettre à jour la disponibilité
        foreach ($books as $book) {
            $book->setIsAvailable(true);
            $this->em->persist($book);
        }
        $borrow->setReturnDateAt(new \DateTimeImmutable());
        $this->em->persist($borrow);

        $this->em->flush();

        return $this->redirectToRoute('admin');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('dueDateAt');
    }
}
