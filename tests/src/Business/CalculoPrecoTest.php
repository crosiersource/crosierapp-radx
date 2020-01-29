<?php


namespace App\Tests\src\Repository;


use App\Business\Estoque\CalculoPreco;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CalculoPrecoTest
 *
 * @package App\Tests\src\Repository
 * @author Carlos Eduardo Pauluk
 */
class CalculoPrecoTest extends KernelTestCase
{

    public function test_calculoPreco(): void
    {
        self::bootKernel();
        /** @var CalculoPreco $calculoPreco */
        $calculoPreco = self::$container->get('test.App\Business\Estoque\CalculoPreco');

        $this->assertInstanceOf(CalculoPreco::class, $calculoPreco);

        $preco1 = [
            'prazo' => 75,
            'margem' => 12.15,
            'custoOperacional' => 35.00,
            'custoFinanceiro' => 0.15,
            'precoCusto' => 39.36,

        ];
        $calculoPreco->calcularPreco($preco1);

        $this->assertEquals(82.9, $preco1['precoPrazo']);
        $this->assertEquals(74.61, $preco1['precoVista']);


        $csvData = file_get_contents(__DIR__ . '/PRECOS_MARGENS_CERTAS.csv');
        $lines = explode(PHP_EOL, $csvData);
        array_shift($lines);

        foreach ($lines as $line) {

            $campos = explode(';', $line);

            if (count($campos) !== 7) {
                continue;
            }

            $preco1 = [
                'prazo' => $campos[6],
                'margem' => DecimalUtils::parseStr($campos[3]),
                'custoOperacional' => DecimalUtils::parseStr($campos[4]),
                'custoFinanceiro' => 0.15,
                'precoCusto' => DecimalUtils::parseStr($campos[0]),
            ];

            $calculoPreco->calcularPreco($preco1);

            $this->assertEquals(DecimalUtils::parseStr($campos[5]), $preco1['coeficiente']);
            $this->assertEquals(DecimalUtils::parseStr($campos[2]), $preco1['precoPrazo']);
            $this->assertEquals(DecimalUtils::parseStr($campos[1]), $preco1['precoVista']);
        }

    }

    public function test_calculoMargem(): void
    {
        self::bootKernel();
        /** @var CalculoPreco $calculoPreco */
        $calculoPreco = self::$container->get('test.App\Business\Estoque\CalculoPreco');

        $this->assertInstanceOf(CalculoPreco::class, $calculoPreco);

        $preco1 = [
            'prazo' => 75,
            // 'margem' => 12.15,
            'custoOperacional' => 35.00,
            'custoFinanceiro' => 0.15,
            'precoCusto' => 39.36,
            'precoPrazo' => 82.9,
            'precoVista' => 74.61,

        ];
        $calculoPreco->calcularMargem($preco1);
        $this->assertEquals(12.15, $preco1['margem']);


        $preco2 = [
            'prazo' => 0,
            'custoOperacional' => 35,
            'custoFinanceiro' => 0.15,
            'precoCusto' => 4.75,
            'precoPrazo' => 8.6,
            'precoVista' => 7,
            'margem' => 0.02,
            'coeficiente' => 1.539,
        ];
        $calculoPreco->calcularMargem($preco2);
        $this->assertEquals(12.15, $preco1['margem']);

        $csvData = file_get_contents(__DIR__ . '/PRECOS_MARGENS_CERTAS.csv');
        $lines = explode(PHP_EOL, $csvData);
        array_shift($lines);

        foreach ($lines as $line) {

            $campos = explode(';', $line);

            if (count($campos) !== 7) {
                continue;
            }

            $preco1 = [
                'prazo' => $campos[6],
                // 'margem' => DecimalUtils::parseStr($campos[3]),
                'custoOperacional' => DecimalUtils::parseStr($campos[4]),
                'custoFinanceiro' => 0.15,
                'precoCusto' => DecimalUtils::parseStr($campos[0]),
                'precoPrazo' => DecimalUtils::parseStr($campos[2]),
                'precoVista' => DecimalUtils::parseStr($campos[1])
            ];

            $calculoPreco->calcularMargem($preco1);

            $diffRazao = (DecimalUtils::parseStr($campos[3]) / (float)$preco1['margem']);
            $diffRazaoInv = $diffRazao > 0 ? round(1 - $diffRazao, 2) : 0;

            // Uma margem de erro de 2%
            $this->assertLessThanOrEqual(0.02, $diffRazaoInv);
        }
    }


    public function _test_calculoMargemGlobal(): void
    {
        self::bootKernel();
        /** @var CalculoPreco $calculoPreco */
        $calculoPreco = self::$container->get('test.App\Business\Estoque\CalculoPreco');

        $this->assertInstanceOf(CalculoPreco::class, $calculoPreco);

        $tentativas = 0;
        for ($precoCusto = 90.00; $precoCusto <= 100; $precoCusto += 0.01) {
            for ($prazo = 90; $prazo <= 90; $prazo += 15) {
                for ($margem = 5.00; $margem <= 12; $margem += 0.05) {
                    $preco = [
                        'prazo' => $prazo,
                        'margem' => $margem,
                        'custoOperacional' => 35.00,
                        'custoFinanceiro' => 0.15,
                        'precoCusto' => $precoCusto,

                    ];
                    $calculoPreco->calcularPreco($preco);
                    $margem = $preco['margem'];

                    $calculoPreco->calcularMargem($preco);

                    if ($margem !== $preco['margem']) {
                        $diffRazao = $preco['margem'] > 0 ? bcdiv($margem, $preco['margem'], 4) : 0.0;
                        $diffRazaoInv = $diffRazao > 0 ? round(1 - $diffRazao, 4) : 0;
                        if ($diffRazaoInv > 0.01) {
                            echo 'MARGEM SETADA: ' . $margem . ' - MARGEM CALC: ' . $preco['margem'] . ' - RAZAO: ' . $diffRazaoInv . PHP_EOL;
                        }
                    }
                    $tentativas++;
//                    $this->assertEquals($margem, $preco['margem']);

                }
            }
        }

        echo PHP_EOL . 'Tentativas: ' . $tentativas;


    }
}