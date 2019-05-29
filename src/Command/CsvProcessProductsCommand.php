<?php

namespace App\Command;

use App\Entity\Tblproductdata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 *  For parsing csv run
 *    php bin/console csv:process:products stock.csv
 *    add --isTest for starting script without saving into database
 *
 * Class CsvProcessProductsCommand
 * @package App\Command
 */
class CsvProcessProductsCommand extends Command
{

    protected static $defaultName = 'csv:process:products';
    private $container;
    private $entityManager;

    public function __construct($name = null, ContainerInterface $container = null)
    {
        parent::__construct($name);
        $this->container = $container;
        $this->entityManager = $this->container->get('doctrine')->getManager();


    }

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
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // asking for file until get it
        while (!$this->isCsvFile($input->getArgument('pathToFile'))) {
            $io->warning('FILE <' .( $input->getArgument('pathToFile') ?: '...'). '> is not scv file');
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
        if (!is_file($pathToFile)) {
            return false;
        }

        /**
         * checking file extension
         */
        if (!preg_match('/\.csv$/', $pathToFile)) {
            return false;
        }
        return true;

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $pathToCsv = $input->getArgument('pathToFile');

        // parse csv file
        $Csv = new \ParseCsv\Csv($pathToCsv);

        $countSuccess = $countErrors = 0;
        $isTest=$input->getOption('isTest');
        if($isTest){
            $io->comment('Test run without saving into database');
        }
        foreach ($Csv->data as $datum) {
            if ($this->saveProductFromData($datum, $io,$isTest )) {
                $countSuccess++;
            } else {
                $countErrors++;
            }
        }

        $io->success(
            'Csv file processed! lines:' . ($countSuccess + $countErrors) .
            ', saved to database:' . $countSuccess .
            ', finished with error:' . $countErrors
        );
    }

    /**
     * saving product with validation
     * @param array $csvRowData
     * @param SymfonyStyle $io
     * @param bool $isTest
     * @return bool
     */
    private function saveProductFromData(array $csvRowData, SymfonyStyle $io, bool $isTest = false): bool
    {
        // validating count of the columns
        if (count($csvRowData) !== 6) {
            $io->warning('Product code "' . $csvRowData['Product Code'] . '" - invalid count of columns!');
            return false;
        }

        // checking for product with the same code
        $repository = $this->container->get('doctrine')->getRepository(Tblproductdata::class);
        $productSameCode = $repository->findOneBy([
            'strproductcode' => $csvRowData['Product Code'],
        ]);
        if ($productSameCode) {
            $io->warning('Product code "' . $csvRowData['Product Code'] . '" -already added!');
            return false;
        }


        // creating new product
        $product = new Tblproductdata();
        $product
            ->setStrProductCode($csvRowData['Product Code'])
            ->setStrProductName($csvRowData['Product Name'])
            ->setStrProductDesc($csvRowData['Product Description'])
            ->setIntstock((int)$csvRowData['Stock'])
            // clear var fo float
            ->setFltCost((float)preg_replace(array('/[^0-9\.\,]/', '/\,/'), array('', '.'), $csvRowData['Cost in GBP']))
            ->setBoolDiscontinued(strtolower($csvRowData['Discontinued']) === 'yes');
        $saved = true;
        try {
            if (!$isTest) {
                $this->entityManager->persist($product);
                $this->entityManager->flush();
            }
        } catch (\Exception $exception) {
            $this->reconnectDb();
            $saved = false;
            $io->warning('Product code "' . $csvRowData['Product Code'] . '" - error saving to database!' . "\n" . $exception->getMessage());
        }

        return $saved;
    }

    /**
     * reopen connection on errors
     */
    private function reconnectDb()
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }
}
