<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CsvProcessProductsCommand extends Command
{
    protected static $defaultName = 'csv:process:products';

    protected function configure()
    {
        $this
            ->setDescription('Processing csv file with products')
            ->addArgument(
                'pathToFile',
                InputArgument::REQUIRED,
                'Set full path to csv file'
            )
            ->addOption(
                'isTest',
                null,
                InputOption::VALUE_NONE,
                'set \'test\' for testing program without saving in database'
            )
        ;
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /**
         * asking for file until get it
         */
        while (!$this->isCsvFile($input->getArgument('pathToFile'))) {
            $io->warning('FILE <'.$input->getArgument('pathToFile').'> is not scv file');
            $input->setArgument('pathToFile', $io->ask('set the path for csv!'));

        }
    }

    /**
     * Check path to csv file
     * @param $pathToFile
     * @return bool
     */
    private function isCsvFile($pathToFile)
    {
        /**
         * checking file existing
         */
        if(!is_file($pathToFile)){
            return false;
        }
        /**
         * checking file extension
         */
        if(!preg_match('/\.csv$/',$pathToFile)){
            return false;
        }
        return true;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $pathToCsv = $input->getArgument('pathToFile');

        $csvContents=file_get_contents($pathToCsv);
        $rows=explode("\n",$csvContents);
        $columnNames=explode(',',array_shift($rows));
        var_dump($columnNames);
        foreach ($rows as $row) {
            $rowCells=explode(',',$row);
            var_dump($rowCells);
        }

        if ($input->getOption('isTest')) {
            // ...
        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
