<?php

namespace App\Repository;

use App\Entity\Book;
use App\Data\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

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
        $query = $this->getSearchQuery($search)->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            6
        );
    }

    public function countItems(SearchData $search): int
    {
        $query = $this->getSearchQuery($search)->getQuery();
        return count($query->getResult());
    }

    /**
     * Récupère les années minimum et maximum correspondant à une recherche
     *
     * @param SearchData $search
     * @return integer[]
     */
    public function findMinMaxDate(SearchData $search): array
    {
        $results = $this->getSearchQuery($search, true)
            ->select('MIN(b.publicationAt) as minDate', 'MAX(b.publicationAt) as maxDate')
            ->getQuery()
            ->getResult();
        return [$results[0]['minDate'], $results[0]['maxDate']];
    }

    public function getSearchQuery(SearchData $search, $ignoreDate = false): QueryBuilder
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

        if (!empty($search->minPublicationAt) && $ignoreDate === false) {
            $minDate = ($search->minPublicationAt);
            dump($minDate);
            $query = $query
                ->andWhere('YEAR(b.publicationAt) >= YEAR(:minDate)')
                ->setParameter('minDate', $minDate);
        }

        if (!empty($search->maxPublicationAt) && $ignoreDate === false) {
            $maxDate = ($search->maxPublicationAt);
            dump($maxDate);
            $query = $query
                ->andWhere('YEAR(b.publicationAt) <= YEAR(:maxDate)')
                ->setParameter('maxDate', $maxDate);
        }

        $query->orderBy('b.name', 'ASC');

        return $query;
    }
}
