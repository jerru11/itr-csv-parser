<?php


namespace App\Tests\Command;

use App\Command\CsvProcessProductsCommand;
use App\Util\Calculator;
use PHPUnit\Framework\TestCase;


class CsvProcessProductsCommandTest extends TestCase
{


    public function getTestParsedData()
    {
        return [
            // testing cost<5 and stock<10
            [
                [
                    'Product Code' => 'asd3',
                    'Product Name' => 'asd1',
                    'Product Description' => 'asd2',
                    'Cost in GBP' => '2',
                    'Stock' => '1',
                ],
                true
            ],
            // testing different count of columns
            [
                [
                    'Product Code' => 'asd3',
                    'Product Name' => 'asd1',
                    'Product Description' => 'asd2',
                    'Cost in GBP' => '2',
                    'Stock' => '1',
                    'Discontinued' => '',
                    '' => 'erwer',
                ],
                true
            ],

            // testing high cost (>1000)
            [
                [
                    'Product Code' => 'asd3',
                    'Product Name' => 'asd1',
                    'Product Description' => '43',
                    'Cost in GBP' => '11111',
                    'Stock' => '1',
                    'Discontinued' => '',
                ],
                true
            ],
            [
                [
                    'Product Code' => 'asd3',
                    'Product Name' => 'asd1',
                    'Product Description' => '43',
                    'Cost in GBP' => '123',
                    'Stock' => '322',
                    'Discontinued' => '',
                ],
                false
            ],
            [
                [
                    'Product Code' => 'asd3',
                    'Product Name' => 'asd1',
                    'Product Description' => '43',
                    'Cost in GBP' => '123',
                    'Stock' => '322',
                    'Discontinued' => 'yes',
                ],
                false
            ],
            // testing errors in cost
            [
                [
                    'Product Code' => 'asd3',
                    'Product Name' => 'asd1',
                    'Product Description' => '43',
                    'Cost in GBP' => '$12,3',
                    'Stock' => '322',
                    'Discontinued' => 'yes',
                ],
                false
            ],

        ];
    }

    /**
     * @dataProvider getTestParsedData
     * @param $testDatum
     * @param $shouldThrow
     * @throws \Exception
     */
    public function testParseOne($testDatum, $shouldThrow)
    {
        $exceptionHavePlace = false;

        try {
            $consoleCsvHandler = new CsvProcessProductsCommand;
            $consoleCsvHandler->saveProductFromData($testDatum, true);
            $message = '';
        } catch (\Exception $exception) {
            $exceptionHavePlace = true;
            $message = $exception->getMessage();
        }
        $this->assertEquals($shouldThrow, $exceptionHavePlace, $message);
    }

}