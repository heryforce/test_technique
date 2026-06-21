<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import:csv',
    description: 'Import data from a csv file',
)]
class ImportCsvCommand extends Command
{
    private $connection;

    public function __construct(private LoggerInterface $customLogger, ManagerRegistry $doctrine)
    {
        $this->connection = $doctrine->getConnection();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'The csv file path')
            ->addOption("separator", "s", InputOption::VALUE_OPTIONAL, "The separator. The default separator is comma (,)", ",");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $separator = $input->getOption("separator");
        $inseeIndex = null;
        $telephoneIndex = null;
        $sqlInsert = [];

        if (!$path) {
            $this->customLogger->error(__FILE__ . ":" . __LINE__ . " :You need to pass the path to the csv file to import.");
            $io->error("You need to pass the path to the csv file to import");

            return Command::FAILURE;
        }
        if (!file_exists($path)) {
            $this->customLogger->error(__FILE__ . ":" . __LINE__ . " : The file does not exist.");
            $io->error("The file does not exist");

            return Command::FAILURE;
        }
        $fileContent = file_get_contents($path);
        if ($fileContent === false) {
            $this->customLogger->error(__FILE__ . ":" . __LINE__ . " :Cannot read the file.");
            $io->error("Cannot read the file");
            return Command::FAILURE;
        }
        $columns = explode(PHP_EOL, $fileContent);
        $titles = explode($separator, $columns[0]);

        foreach ($titles as $index => $title) {
            if ($title === 'insee') {
                $inseeIndex = $index;
            }
            if ($title === 'telephone') {
                $telephoneIndex = $index;
            }
        }
        if ($telephoneIndex === null || $inseeIndex === null) {
            $this->customLogger->error(__FILE__ . ":" . __LINE__ . " :The file must have a 'insee' column and a 'telephone' column.");
            $io->error("The file must have a 'insee' column and a 'telephone' column");

            return Command::FAILURE;
        }

        $nbErrors = 0;
        foreach ($columns as $index => $values) {
            if ($index === 0) {
                continue;
            }
            $data = explode(',', $values);
            if (!isset($data[$inseeIndex])) {
                $this->customLogger->error("Error : no insee value.");
                $nbErrors++;
                continue;
            }
            if (!isset($data[$telephoneIndex])) {
                $this->customLogger->error("Error : no telephone value.");
                $nbErrors++;
                continue;
            }
            $inseeValue = $data[$inseeIndex];
            $telephoneValue = $data[$telephoneIndex];
            $inseeOk = false;
            $telephoneOk = false;

            if ($inseeValue && preg_match("/^[0-9]{5}$/", trim($inseeValue)) === 1) {
                $inseeOk = true;
            }
            if ($telephoneValue && preg_match("/^(?:\+33|0){1}[1-9]{1}\d{8}$/", trim($telephoneValue)) === 1) {
                $telephoneOk = true;
            }

            if ($telephoneOk === true && $inseeOk === true) {
                $sqlInsert[] = "INSERT INTO contact(insee, telephone) VALUES('" . trim($inseeValue) . "', '" . trim($telephoneValue) . "')";
                $this->customLogger->notice("New line in the contact table. Values : $inseeValue - $telephoneValue");
            } else {
                $this->customLogger->error("Error : line with values $inseeValue - $telephoneValue has not been inserted.");
                $nbErrors++;
            }
        }
        foreach ($sqlInsert as $query) {
            $stmt = $this->connection->prepare($query);
            $stmt->executeStatement();
        }
        $nbInsertedLines = count($sqlInsert);
        $this->customLogger->notice("End of process. Number of lines inserted : $nbInsertedLines. Number of errors : $nbErrors");
        $io->success("Done. Number of lines inserted : $nbInsertedLines. Number of errors : $nbErrors");

        return Command::SUCCESS;
    }
}
