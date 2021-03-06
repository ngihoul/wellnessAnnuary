<?php

namespace App\Repository;

use App\Entity\Provider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Provider|null find($id, $lockMode = null, $lockVersion = null)
 * @method Provider|null findOneBy(array $criteria, array $orderBy = null)
 * @method Provider[]    findAll()
 * @method Provider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Provider::class);
    }

    /**
     * Query for search feature - By name OR/AND By Category OR/AND By localization
     * @param null $what
     * @param int $whichCategory
     * @param null $where
     * @param int $offset
     * @return mixed
     */
    public function findBySearch($what = null, $whichCategory = 0, $where = null, $offset = 0): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('u.locality', 'l')
            ->join('l.postCode', 'pc')
            ->join('pc.municipality', 'm')
            ->join('p.serviceCategories', 'c');

        if($what) {
            $queryBuilder
                ->andWhere('p.name LIKE :what OR p.description LIKE :what')
                ->setparameter('what', '%'.$what.'%');
        }

        if($where) {
            $queryBuilder
                ->andWhere('l.name LIKE :where OR m.name LIKE :where OR pc.postCode LIKE :where')
                ->setparameter('where', '%'.$where.'%');
        }

        if($whichCategory) {
            $queryBuilder
                ->andWhere('c.id = :whichCategory')
                ->setparameter(':whichCategory', $whichCategory);
        }

        $queryBuilder
            ->andWhere('u.isVerified = 1')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($queryBuilder);
    }

    public function findByCategory($category, $offset): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->join('p.serviceCategories', 'c')
            ->join('p.user', 'u')
            ->andWhere('c.id = :id')
            ->andWhere('u.isVerified = 1')
            ->setParameter(':id', $category)
            ->orderBy('u.registeredOn', 'DESC')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($queryBuilder, true);
    }

    public function findForAutoCompletion($query) {
        return $this->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'p.description')
            ->andWhere('p.name LIKE :value OR p.description LIKE :value')
            ->andWhere('u.isVerified = 1')
            ->setParameter(':value', '%'.$query.'%')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function getLastSubscribers($start, $offset): Paginator
    {
        $query = $this->createQueryForLastSubscribers()
            ->setFirstResult($start)
            ->setMaxResults($offset)
            ->getQuery();

        return $paginator = new Paginator($query, true);
    }

    private function createQueryForLastSubscribers() {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->andWhere('u.isVerified = 1')
            ->orderBy('u.registeredOn', 'DESC')
            ->addOrderBy('p.name', 'ASC');
    }

    public function findSimilar($provider) {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('u.locality', 'l')
            ->join('l.postCode', 'pc')
            ->join('pc.municipality', 'm')
            ->join('p.serviceCategories', 'c')
            // Municipality = Provider.municipality AND not the selected provider
            ->andWhere('m.name = :municipality AND u.id != :id')
            ->setParameter(':municipality', $provider->getUser()->getLocality()->getPostCode()->getMunicipality()->getName())
            ->setParameter(':id', $provider->getUser()->getId())
            // Category IS Provider.category (multiple categories)
            ->andWhere($this->createQueryBuilder('c')->expr()->in('c', ':c'))
            ->andWhere('u.isVerified = 1')
            ->setParameter(':c', $provider->getServiceCategories())
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
