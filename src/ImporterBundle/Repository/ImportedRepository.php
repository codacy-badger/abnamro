<?php

namespace ImporterBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ImporterBundle\Entity\Imported;

/**
 * ImportedRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImportedRepository extends \Doctrine\ORM\EntityRepository
{
    /*
     * @param string $filename
     *
     */
    public function getTransactionByFileName($filename)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb
            ->select('i')
            ->from('ImporterBundle:Imported', 'i')
            ->where('i.fileName = ?1')
            ->setParameter(1, $filename)->getQuery();

        return $query->getResult();
    }
}
