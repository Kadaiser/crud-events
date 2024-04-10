<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TruncateTablesCommand extends Command
{
    protected static $defaultName = 'app:truncate-tables';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');

        $schemaManager = $connection->getSchemaManager();
        $tables = $schemaManager->listTables();
        foreach ($tables as $table) {
            $connection->query('TRUNCATE '.$table->getName());
        }

        $connection->query('SET FOREIGN_KEY_CHECKS=1');

        $output->writeln('All tables truncated.');

        return Command::SUCCESS;
    }
}