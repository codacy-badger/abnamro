<?php

// src/AppBundle/Command/GreetCommand.php
namespace TransactionsBundle\Command;

use TransactionsBundle\Entity\Transactions;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('import:csv')
            ->setDescription('import transactions')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'locations of the file'
            )
            ->addArgument(
                'account',
                InputArgument::OPTIONAL,
                'user account'
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
        $fileLocation = $input->getArgument('name');
        $account = $input->getArgument('account');
        $fileContent = file_get_contents($fileLocation);
        $fileContentArray = explode( "\n", $fileContent);

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        if ($fileContent) {
            dump('Start importing');
            dump(count($fileContentArray));

            // Identify File type
            preg_match("/\.csv/", $fileLocation) ? $limit = ";" : $limit = "\t";

            foreach ($fileContentArray as $line) {
                // Clean end of string
                $line = rtrim($line);
                if ( empty($line) ) {
                    continue;
                }

                # Generate Hash
                $hashString = $line;
                $hash = hash('md5', $hashString, False);
                $verify = $em
                    ->getRepository('TransactionsBundle:Transactions')
                    ->getTransactionByHash($hash);

                // Check if this is already on DB and if so continue
                // Should probably clean this a bit
                if ($verify['id'] > 0) {
                    $line = '';
                    continue;
                }

                $info = explode($limit, $line);
                if ($limit == ";") {
                    if (!preg_match('/[a-zA-Z]/',$info[0])) {
                        $Data = new \DateTime($info[0]);
                        $description = $info[2];
                        $value = $info[3];
                        $saldo = $info[5];
                    }
                    continue;
                }

                $correctDate = substr($info[2],0,4).
                    '-'.substr($info[2],4,2).'-'.
                    substr($info[2],6,2);
                $Date = new \DateTime($correctDate);

                $transaction = new Transactions();

                $transaction->setTransactionHash($hash);
                $transaction->setCreateAt($Date);
                $transaction->setAmount(
                    floatval(
                        str_replace(',', '.', str_replace('.', '', $info[6]))
                    )
                );
                $transaction->setstartsaldo(
                    floatval(
                        str_replace(',', '.', str_replace('.', '', $info[3]))
                    )
                );
                $transaction->setEndsaldo(
                    floatval(
                        str_replace(',', '.', str_replace('.', '', $info[4]))
                    )
                );
                $transaction->setDescription($info[7]);
                $transaction->setShortDescription('');
                $transaction->setAccountId($account);

                $em->persist($transaction);
                $em->flush();
            }
            return;
        }
        print "Please use a csv file with content";
    }
}
?>
