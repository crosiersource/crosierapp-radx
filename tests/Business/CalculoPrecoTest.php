<?php


namespace Tests\Business;


use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibRadxBundle\Business\Estoque\CalculoPreco;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CalculoPrecoTest
 *
 * @package App\Tests\src\Repository
 * @author Carlos Eduardo Pauluk
 */
class CalculoPrecoTest extends KernelTestCase
{

    private CalculoPreco $calculoPreco;

    /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->calculoPreco = self::$container->get('CrosierSource\CrosierLibRadxBundle\Business\Estoque\CalculoPreco');
        $this->assertInstanceOf(CalculoPreco::class, $this->calculoPreco);
    }

    public function test_calculoPreco(): void
    {
        $preco1 = [
            'prazo' => 75,
            'margem' => 0.1215,
            'custoOperacional' => 0.35,
            'custoFinanceiro' => 0.15,
            'precoCusto' => 39.36,
        ];
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(82.9, $preco1['precoPrazo']);
        $this->assertEquals(74.61, $preco1['precoVista']);



        $preco1 = [
            'prazo' => 75,
            'margem' => 0.12,
            'custoOperacional' => 0.35,
            'custoFinanceiro' => 0.15,
        ];

        $preco1['precoCusto'] = 24.64;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.7, $preco1['precoPrazo']);
        $this->assertEquals(46.53, $preco1['precoVista']);

        $preco1['precoCusto'] = 24.65;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.8, $preco1['precoPrazo']);
        $this->assertEquals(46.62, $preco1['precoVista']);
        
        $preco1['precoCusto'] = 24.67;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.8, $preco1['precoPrazo']);
        $this->assertEquals(46.62, $preco1['precoVista']);
        
        $preco1['precoCusto'] = 24.68;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.8, $preco1['precoPrazo']);
        $this->assertEquals(46.62, $preco1['precoVista']);
        
        $preco1['precoCusto'] = 24.69;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.8, $preco1['precoPrazo']);
        $this->assertEquals(46.62, $preco1['precoVista']);

        $preco1['precoCusto'] = 24.70;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.9, $preco1['precoPrazo']);
        $this->assertEquals(46.71, $preco1['precoVista']);

        $preco1['precoCusto'] = 24.71;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.9, $preco1['precoPrazo']);
        $this->assertEquals(46.71, $preco1['precoVista']);

        $preco1['precoCusto'] = 24.72;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.9, $preco1['precoPrazo']);
        $this->assertEquals(46.71, $preco1['precoVista']);

        $preco1['precoCusto'] = 24.73;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(51.9, $preco1['precoPrazo']);
        $this->assertEquals(46.71, $preco1['precoVista']);

        $preco1['precoCusto'] = 24.74;
        $this->calculoPreco->calcularPreco($preco1);
        $this->assertEquals(52, $preco1['precoPrazo']);
        $this->assertEquals(46.80, $preco1['precoVista']);


        $csvData = file_get_contents(__DIR__ . '/PRECOS_MARGENS_CERTAS.csv');
        $lines = explode(PHP_EOL, $csvData);
        array_shift($lines);

        $i=1;
        foreach ($lines as $line) {

            $campos = explode(';', $line);

            if (count($campos) !== 7) {
                continue;
            }

            $preco1 = [
                'prazo' => $campos[6],
                'margem' => bcdiv(DecimalUtils::parseStr($campos[3]), 100, 4),
                'custoOperacional' => bcdiv(DecimalUtils::parseStr($campos[4]), 100, 4),
                'custoFinanceiro' => 0.15,
                'precoCusto' => DecimalUtils::parseStr($campos[0]),
            ];

            $this->calculoPreco->calcularPreco($preco1);

            $this->assertEquals(DecimalUtils::parseStr($campos[5]), $preco1['coeficiente']);
            
            $this->assertEquals(DecimalUtils::parseStr($campos[2]), $preco1['precoPrazo']);
            $this->assertEquals(DecimalUtils::parseStr($campos[1]), $preco1['precoVista']);
            echo $i++ . PHP_EOL;
        }

    }

    public function test_calculoMargem(): void
    {
        $preco1 = [
            'prazo' => 75,
            // 'margem' => 12.15,
            'custoOperacional' => 0.35,
            'custoFinanceiro' => 0.15,
            'precoCusto' => 39.36,
            'precoPrazo' => 82.9,
            'precoVista' => 74.61,

        ];
        $this->calculoPreco->calcularMargem($preco1);
        $this->assertEquals(12.15, $preco1['margem']);


        $preco2 = [
            'prazo' => 0,
            'custoOperacional' => 0.35,
            'custoFinanceiro' => 0.15,
            'precoCusto' => 4.75,
            'precoPrazo' => 8.6,
            'precoVista' => 7,
            'margem' => 0.02,
            'coeficiente' => 1.539,
        ];
        $this->calculoPreco->calcularMargem($preco2);
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
                'custoOperacional' => bcdiv(DecimalUtils::parseStr($campos[4]), 100, 4),
                'custoFinanceiro' => 0.15,
                'precoCusto' => DecimalUtils::parseStr($campos[0]),
                'precoPrazo' => DecimalUtils::parseStr($campos[2]),
                'precoVista' => DecimalUtils::parseStr($campos[1])
            ];

            $this->calculoPreco->calcularMargem($preco1);

            $diffRazao = (DecimalUtils::parseStr($campos[3]) / (float)$preco1['margem']);
            $diffRazaoInv = $diffRazao > 0 ? round(1 - $diffRazao, 2) : 0;

            // Uma margem de erro de 2%
            $this->assertLessThanOrEqual(0.02, $diffRazaoInv);
        }
    }


    public function _test_calculoMargemGlobal(): void
    {
        $tentativas = 0;
        for ($precoCusto = 90.00; $precoCusto <= 100; $precoCusto += 0.01) {
            for ($prazo = 90; $prazo <= 90; $prazo += 15) {
                for ($margem = 5.00; $margem <= 12; $margem += 0.05) {
                    $preco = [
                        'prazo' => $prazo,
                        'margem' => $margem,
                        'custoOperacional' => 0.35,
                        'custoFinanceiro' => 0.15,
                        'precoCusto' => $precoCusto,

                    ];
                    $this->calculoPreco->calcularPreco($preco);
                    $margem = $preco['margem'];

                    $this->calculoPreco->calcularMargem($preco);

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