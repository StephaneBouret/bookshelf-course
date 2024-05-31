<?php

namespace App\Repository;

use App\Entity\Book;
use App\Data\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    /**
     * @var $paginator
     */
    protected $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Book::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les livres en lien avec une recherche
     *
     * @return PaginationInterface
     */
    public function findSearch(SearchData $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('b')
            ->select('c', 'b')
            ->select('a', 'b')
            ->join('b.category', 'c')
            ->join('b.author', 'a');

        if (!empty($search->q)) {
           $query = $query
                ->andWhere('b.name LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if (!empty($search->categories)) {
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }

        if (!empty($search->author)) {
            $query = $query
                 ->andWhere('a.lastname LIKE :a')
                 ->setParameter('a', "%{$search->author}%");
         }

        $query = $query->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            6
        );
    }
}
