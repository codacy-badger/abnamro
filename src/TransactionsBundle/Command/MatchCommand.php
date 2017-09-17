<?php

// src/AppBundle/Command/GreetCommand.php
namespace TransactionsBundle\Command;

use \Entity\Transactions;
use Categories\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class MatchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('match:payments')
            ->setDescription('Match Payments of a Certain Type')
            ->addArgument(
                'transaction_type',
                InputArgument::OPTIONAL,
                'id of the transaction type to match'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $category = $input->getArgument('transaction_type');
        $em = $this->getContainer()->get('doctrine')->getManager();

        // List of Transactions Without Category
        $transactions = $em
            ->getRepository('TransactionsBundle:Transactions')
            ->findBy(array('categories' => null));

        foreach ($transactions as $key => $transaction) {
            if (
                $transaction->getPossibleMatch()
                || $transaction->getCategories()
            ) {
                unset($transactions[$key]);
                continue;
            }
        }

        if ($category === "all") {
            dump('Mattching all Types');
            $categories = $em
                ->getRepository('CategoriesBundle:Categories')
                ->findAllNotNUll();

            foreach ($categories as $category) {
                dump("Matching ".$category->getName()." : ");

                $matchedT = $em
                    ->getRepository('TransactionsBundle:Transactions')
                    ->findBy(
                        array(
                            'categories' => $category->getId()
                        )
                    );

                $this->cycleTransactions(
                    $matchedT,
                    $transactions,
                    $category->getId(),
                    $output
                );
            }
        }

        if ($category !== "all") {
            $matchedT = $em
                ->getRepository('TransactionsBundle:Transactions')
                ->findBy(
                    array(
                        'categories' => $category
                    )
                );

            $this->cycleTransactions(
                $matchedT,
                $transactions,
                $category,
                $output
            );
        }
    }

    protected function cycleTransactions(
        $matchedT,
        $transactions,
        $category,
        $output
    ) {
        dump('Starting '.date('h:i:s A'));

        $progress = new ProgressBar($output, count($matchedT));
        $progress->setFormat('verbose');

        $results = array();

        $matchService = $this
            ->getApplication()
            ->getKernel()
            ->getContainer()
            ->get('transactions.match');

        $progress->start();
        foreach ($matchedT as $match) {
            $progress->advance();
            if (!$match->getCategories()) {
                continue;
            }

            $matches = $matchService
                ->match($match, $transactions, $category, $output);

            if (count($matches) === 0) {
                continue;
            }

            foreach ($matches as $matchR) {
                $results
                    [$match->getCategories()->getId()]
                    [$matchR->getId()] = $matchR;
            }
        }
        $progress->finish();
        dump('Ended '.date('h:i:s A'));
    }
}
