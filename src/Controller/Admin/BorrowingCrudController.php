<?php

namespace App\Controller\Admin;

use App\Entity\Borrowing;
use App\Form\Type\CustomDateType;
use App\Service\SendMailService;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class BorrowingCrudController extends AbstractCrudController
{
    protected $em;
    protected $sendMailService;
    protected $adminUrlGenerator;

    public function __construct(EntityManagerInterface $em, SendMailService $sendMailService, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->em = $em;
        $this->sendMailService = $sendMailService;
        $this->adminUrlGenerator = $adminUrlGenerator;
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
        $sendReminderMail = Action::new('sendReminder', 'Envoyer un mail de relance', 'fas fa-envelope')
            ->linkToCrudAction('sendReminder')
            ->displayIf(fn ($entity) => $entity->isOverdue());

        $returnDateBorrowing  = Action::NEW('returnDate', 'Retour du livre', 'fas fa-reply')
            ->linkToCrudAction('returnDate')
            ->displayIf(fn ($entity) => $entity->isOverdue());

        $actions = parent::configureActions($actions);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $sendReminderMail)
            ->add(Crud::PAGE_INDEX, $returnDateBorrowing)
            // Supprime l'édition
            ->disable(Action::EDIT)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
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
        $books = $borrow->getBooks();
        foreach ($books as $book) {
            $book->setIsAvailable(true);
            $this->em->persist($book);
        }
        $borrow->setReturnDateAt(new \DateTimeImmutable())
            ->setIsOverdue(true);
        $this->em->persist($borrow);

        $this->em->flush();

        return $this->redirectToRoute('admin');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(BooleanFilter::new('isOverdue', 'Non restitution'));
    }

    public function sendReminder(AdminContext $context): Response
    {
        $borrow = $context->getEntity()->getInstance();
        $user = $borrow->getUser();
        $email = $user->getEmail();

        $this->sendMailService->sendEmail(
            'contact@bookshelf.discommentondit.com',
            'Bibliothèque',
            $email,
            'Relance de retour de livre',
            'reminder_email',
            [
                'user' => $user,
                'borrow' => $borrow,
            ]
        );

        $this->addFlash('success', 'Le mail de relance a bien été envoyé !');

        $url = $this->adminUrlGenerator
            ->setController(BorrowingCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }
}
