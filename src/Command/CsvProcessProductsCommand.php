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
        if ($container) {
            $this->container = $container;
            $this->entityManager = $this->container->get('doctrine')->getManager();
        }

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
            $io->warning('FILE <' . ($input->getArgument('pathToFile') ?: '...') . '> is not scv file');
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
        $isTest = $input->getOption('isTest');
        if ($isTest) {
            $io->comment('Test run without saving into database');
        }
        foreach ($Csv->data as $datum) {
            try {
                $this->saveProductFromData($datum, $isTest);
                $countSuccess++;
            } catch (\Exception $exception) {
                $io->warning($exception->getMessage());
                // for case of sql connection closing because of error
                $this->reconnectDb();
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
     * @param bool $isTest
     * @throws \Exception
     */
    public function saveProductFromData(array $csvRowData, bool $isTest = false): void
    {
        // validating count of the columns
        if (count($csvRowData) !== 6) {
            throw new \Exception('Product code "' . $csvRowData['Product Code'] . '" - invalid count of columns!');
        }

        // checking for product with the same code
        if ($this->container) {
            $repProducts = $this->container->get('doctrine')->getRepository(Tblproductdata::class);
            $productSameCode = $repProducts->findOneBy([
                'strproductcode' => $csvRowData['Product Code'],
            ]);
            if ($productSameCode) {
                throw new \Exception('Product code "' . $csvRowData['Product Code'] . '" - already exists!');
            }
        }

        $productCost = (float)preg_replace(array('/[^0-9\.\,]/', '/\,/'), array('', '.'), $csvRowData['Cost in GBP']);
        $productStock = (int)$csvRowData['Stock'];

        $minCost = 5;
        $minStock = 10;
        if ($productCost < $minCost && $productStock < $minStock) {
            throw new \Exception('Product code "' . $csvRowData['Product Code'] . '" -  costs less that $' . $minCost . ' and has less than ' . $minStock . ' stock!');
        }
        $maxCost = 1000;
        if ($productCost > $maxCost) {
            throw new \Exception('Product code "' . $csvRowData['Product Code'] . '" -  cost over $' . $maxCost . ' !');
        }


        // creating new product
        $product = new Tblproductdata();
        $product
            ->setStrProductCode($csvRowData['Product Code'])
            ->setStrProductName($csvRowData['Product Name'])
            ->setStrProductDesc($csvRowData['Product Description'])
            ->setIntstock($productStock)
            ->setFltCost($productCost);

        if (strtolower($csvRowData['Discontinued']) === 'yes') {
            $product->setDtmDiscontinued(new  \DateTime());
        }


        if (!$isTest) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }

    }

    /**
     * reopen connection on errors
     */
    private function reconnectDb(): void
    {
        if ($this->entityManager && !$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }
}
