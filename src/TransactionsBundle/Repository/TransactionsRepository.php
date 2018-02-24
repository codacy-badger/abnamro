<?php

namespace TransactionsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use CategoriesBundle\Entity\Categories;

/**
* TransactionsRepository
*
* This class was generated by the Doctrine ORM. Add your own custom
* repository methods below.
*/
class TransactionsRepository extends EntityRepository
{
    public function getMonthIncome($month, $year)
    {
        $data = $this->getEntityManager()->createQuery(
            "SELECT t
            FROM TransactionsBundle:Transactions t
            WHERE MONTH(t.createAt) = $month
            AND YEAR(t.createAt) = $year
            AND t.amount > 0"
        )->execute();

        return $data;
    }

    public function getMonthSpending($month, $year)
    {
        $data = $this->getEntityManager()->createQuery(
            "SELECT t
            FROM TransactionsBundle:Transactions t
            WHERE MONTH(t.createAt) = $month
            AND YEAR(t.createAt) = $year
            AND t.amount < 0"
        )->execute();

        return $data;
    }

    public function getMonths($year)
    {
        $months = $this->getEntityManager()
            ->createQuery(
                "SELECT DISTINCT Month(p.createAt) as months, p.createAt as monthName
                FROM TransactionsBundle:Transactions p
                WHERE Year(p.createAt) = $year
                GROUP BY months"
            )->execute();

        return $months;
    }

    public function getAllYears()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT DISTINCT YEAR(p.createAt) as year
                FROM TransactionsBundle:Transactions p
                ORDER BY p.createAt"
            )->getResult();
    }

    public function findAllByMonth($month, $year)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT p
                FROM TransactionsBundle:Transactions p
                WHERE MONTH(p.createAt) = $month
                AND Year(p.createAt) = $year
                ORDER BY p.createAt ASC"
            )->getResult();
    }

    public function getmonth($month, $year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT SUM(p.amount)
                FROM TransactionsBundle:Transactions p
                WHERE MONTH(p.createAt) = $month
                AND Year(p.createAt) = $year
                GROUP BY p.shortDescription"
            )->execute();

        return $data;
    }

    public function getMatchTransactions($transactionId)
    {
        $transaction = $this->getEntityManager()
            ->createQuery(
                "SELECT p.amount, p.description
                FROM TransactionsBundle:Transactions p
                WHERE p.id = $transactionId"
            )->execute();

        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT p.id, p.createAt, p.amount, p.description, t.name
                FROM TransactionsBundle:Transactions p
                JOIN CategoriesBundle:Categories t
                WHERE p.categories = t.id"
            )->execute();

        return array('data' => $data, 'transaction' => $transaction);
    }

    public function getDescriptionUsage($month, $year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT t.name as shortDescription, sum(p.amount) as total, count(p.categories) as ocurrencies
                FROM TransactionsBundle:Transactions p
                JOIN CategoriesBundle:Categories t
                WHERE p.categories = t.id
                AND Month(p.createAt) = $month
                AND Year(p.createAt) = $year
                AND t.name != ''
                GROUP BY t.name"
            )->execute();

        return $data;
    }

    public function getDescriptionUsageYear($year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT t.name as shortDescription, sum(p.amount) as total, count(p.categories) as ocurrencies
                FROM TransactionsBundle:Transactions p
                JOIN CategoriesBundle:Categories t
                WHERE p.categories = t.id
                AND Year(p.createAt) = $year
                AND t.name != ''
                GROUP BY t.name"
            )->execute();

        return $data;
    }

    public function getDescriptionPerDayInMonth($month, $year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT sum(p.amount) as total, Day(p.createAt) as day
                FROM TransactionsBundle:Transactions p
                WHERE Month(p.createAt) = $month
                AND Year(p.createAt) = $year
                AND p.shortDescription != ''
                GROUP BY day"
            )->execute();

        return $data;
    }

    public function getMonthsForName($year)
    {
        $months = $this->getEntityManager()
            ->createQuery(
                "SELECT DISTINCT Month(p.createAt) as months
                FROM TransactionsBundle:Transactions p
                WHERE Year(p.createAt) = $year
                AND p.shortDescription != 'savings'
                ORDER BY months"
            )->execute();

        return $months;
    }

    public function graphMonthYear($year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT sum(p.amount) as amount, Month(p.createAt) as month
                FROM TransactionsBundle:Transactions p
                WHERE
                Year(p.createAt) = $year
                GROUP BY month"
            )->execute();

        return $data;
    }

    public function getDescriptionPerMonth($month, $year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT DISTINCT  t.name  as description,
                sum(p.amount) as amount,
                t.recurring as recurring
                FROM TransactionsBundle:Transactions p
                JOIN CategoriesBundle:Categories t
                WHERE p.categories = t.id
                AND Month(p.createAt) = $month
                AND Year(p.createAt) = $year
                GROUP BY description
                ORDER BY p.createAt"
            )->execute();

        return $data;
    }

    public function getSpendsPerDay($month, $year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT sum(p.amount)
                FROM TransactionsBundle:Transactions p
                WHERE MONTH(p.createAt) = $month
                AND YEAR(p.createAt) = $year
                AND p.amount < 0"
            )->execute();

        return $data;
    }

    public function getAmountPerDay($month, $year)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT DAY(p.createAt) as days, p.startsaldo as amount
                FROM TransactionsBundle:Transactions p
                WHERE YEAR(p.createAt) = $year
                AND MONTH(p.createAt) = $month
                GROUP BY days
                ORDER BY p.id DESC"
            )->execute();

        return $data;
    }

    public function getTransactionByHash($hash)
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT p.id
                FROM TransactionsBundle:Transactions p
                WHERE p.transactionHash = '$hash'"
            )->getOneOrNullResult();

        return $data;
    }

    // Get Income/Expenses per Month of current Year
    public function getIncomeExpensiveYear($year, $type = 0)
    {
        if ($type == 1) {
            $data = $this->getEntityManager()
                ->createQuery(
                    "SELECT Month(p.createAt) as month, sum(p.amount) as amount
                    FROM TransactionsBundle:Transactions p
                    where
                    YEAR(p.createAt) = $year
                    and
                    p.amount > $type
                    group by month"
                )->execute();

                return $data;
        }

        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT Month(p.createAt) as month, sum(p.amount) as amount
                FROM TransactionsBundle:Transactions p
                where
                YEAR(p.createAt) = $year
                and
                p.amount < $type
                group by month"
            )->execute();

        return $data;
    }

    public function groupByYear()
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT YEAR(p.createAt) as year, Month(p.createAt) as month, count(p.id), SUM(p.amount)
                FROM TransactionsBundle:Transactions p
                GROUP BY month, year
                ORDER BY Year(p.createAt), Month(p.createAt)"
            )->execute();

        return $data;
    }

    public function getMatched()
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT YEAR(p.createAt) as year, Month(p.createAt) as month, count(p.id), SUM(p.amount)
                FROM TransactionsBundle:Transactions p
                WHERE p.categories is not null
                GROUP BY month, year
                ORDER BY Year(p.createAt), Month(p.createAt)"
            )->execute();

        return $data;
    }

    public function getPossibleMatch()
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT (p.possibleMatch) as possibleMatch, count(p.id)
                FROM TransactionsBundle:Transactions p
                WHERE p.possibleMatch is not null
                GROUP BY p.possibleMatch"
            )->execute();

        return $data;
    }

    public function getPrevision()
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT t, c
                FROM TransactionsBundle:Transactions t
                JOIN CategoriesBundle:Categories c
                WHERE t.categories = c.id
                AND c.recurring = 1
                ORDER BY t.createAt ASC"
            )->execute();

        return $data;
    }

    public function getYears()
    {
        $data = $this->getEntityManager()
            ->createQuery(
                "SELECT YEAR(t.createAt) as year
                FROM TransactionsBundle:Transactions t
                GROUP BY year"
            )->execute();

        return $data;
    }

    // public function getToBeMatched()
    // {
    //     $qb = $this->createQueryBuilder('p');
    //     $qb->where($qb->expr()->not($qb->expr()->eq('p.possibleMatch', '?1')));
    //     $qb->setParameter(1, null);
    //
    //     return $qb->getQuery();
    // }
}
