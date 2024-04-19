<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\Type\CustomDateType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Livres')
            ->setPageTitle('new', 'Créer un livre')
            ->setPaginatorPageSize(10)
            ->setEntityLabelInSingular('un Livre');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            FormField::addColumn(6),
            TextField::new('name', 'Nom du livre'),
            TextEditorField::new('description', 'Description du livre')->hideOnIndex(),
            AssociationField::new('category', 'Catégorie du livre')->autocomplete(),
            AssociationField::new('author', 'Auteur du livre')->autocomplete(),
            AssociationField::new('state', 'Etat du livre')->hideOnIndex(),
            DateField::new('publicationAt', 'Date d\'édition du livre')
                ->setFormType(CustomDateType::class)
                ->setFormat('yyyy')
                ->renderAsChoice(),
            FormField::addColumn(6),
            // UrlField::new('picture', 'URL de la photo')->hideOnIndex(),
            TextField::new('imageFile', 'Fichier image :')
                ->setFormType(VichImageType::class)
                ->setTranslationParameters(['form.label.delete' => 'Supprimer l\'image'])
                ->hideOnIndex(),
            ImageField::new('imageName', 'Image')
                ->setBasePath('/images/books')
                ->onlyOnIndex(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('state');
    }
}
