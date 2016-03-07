<?php

namespace AccountBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TransactionsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TransactionsRepository extends EntityRepository
{
  public function findAllByMonth($month)
  {
    return $this->getEntityManager()
      ->createQuery(
        "SELECT p
        FROM AccountBundle:Transactions p
        WHERE MONTH(p.createAt) = $month
        ORDER BY p.createAt ASC"
      )
      ->getResult();
  }

  public function getCurrentMonth($month)
  {
     $dataGraph = $this->getEntityManager()
      ->createQuery(
        "SELECT SUM(p.amount)
        FROM AccountBundle:Transactions p
        WHERE MONTH(p.createAt) = $month
        GROUP BY p.shortDescription"
      )
      ->execute();

      return $dataGraph;
  }

  public function getDescriptionUsage($month)
  {
    $data = $this->getEntityManager()
      ->createQuery(
        "SELECT p.shortDescription, sum(p.amount) as total, count(p.shortDescription) as ocurrencies
        FROM AccountBundle:Transactions p
        WHERE Month(p.createAt) = $month
        AND p.shortDescription != ''
        AND p.shortDescription != 'savings'
        GROUP BY p.shortDescription"
      )
      ->execute();

      return $data;
  }

  public function  getDescriptionPerDayInMonth($month)
  {
    $data = $this->getEntityManager()
      ->createQuery(
        "SELECT sum(p.amount) as total, Day(p.createAt) as day
        FROM AccountBundle:Transactions p
        WHERE Month(p.createAt) = $month
        AND p.shortDescription != ''
        AND p.shortDescription != 'savings'
        GROUP BY day"
      )
      ->execute();

      return $data;
  }

  public function getMonths($year)
  {
    $months = $this->getEntityManager()
      ->createQuery(
        "SELECT DISTINCT Month(p.createAt) as months
        FROM AccountBundle:Transactions p
        WHERE Year(p.createAt) = $year
        ORDER BY months"
      )
      ->execute();

      return $months;
  }
}
