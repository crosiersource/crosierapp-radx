<?php

namespace App\Twig;

use App\Entity\Estoque\Atributo;
use App\Repository\Estoque\AtributoRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class CrosierCoreAssetExtension
 *
 * @package CrosierSource\CrosierLibBaseBundle\Twig
 * @author Carlos Eduardo Pauluk
 */
class BuildAtributoInputExtension extends AbstractExtension
{

    /**
     * @var EntityManagerInterface
     */
    protected $doctrine;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EntityManagerInterface $doctrine, LoggerInterface $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('buildAtributoInput', [$this, 'buildAtributoInput'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $prefixo
     * @param string $id
     * @param string $tipo
     * @param string|null $valor
     * @return string
     */
    function buildAtributoInput(string $form, string $prefixo, string $id, string $tipo, ?string $valor = null)
    {
        /** @var AtributoRepository $repoAtributo */
        $repoAtributo = $this->doctrine->getRepository(Atributo::class);
        /** @var Atributo $atributoPai */
        $atributoPai = $repoAtributo->find($id);

        try {
            switch ($tipo) {
                case 'STRING':
                    $r = '<div class="input-group">';
                    if ($atributoPai->getPrefixo()) {
                        $r .= '<div class="input-group-prepend"><span class="input-group-text">' . $atributoPai->getPrefixo() . '</span></div>';
                    }
                    $outrasClasses = $atributoPai->getConfig() ?? '';
                    $r .= '<input type="text" form="' . $form . '" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . ']" class="form-control ' . $outrasClasses . '" value="' . $valor . '"' . ($atributoPai->getEditavel() === 'N' ? ' readonly ' : '') . '>';
                    if ($atributoPai->getSufixo()) {
                        $r .= '<div class="input-group-append"><span class="input-group-text">' . $atributoPai->getSufixo() . '</span></div>';
                    }
                    $r .= '</div>';
                    return $r;
                case 'HTML':
                    $r = '<div class="w-100">';
                    $r .= '<textarea form="' . $form . '" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . ']" class="form-control summernote"' . ($atributoPai->getEditavel() === 'N' ? ' readonly ' : '') . '>' . $valor . '</textarea>';
                    $r .= '</div>';
                    return $r;
                case 'INTEGER':
                    $r = '<div class="input-group">';
                    if ($atributoPai->getPrefixo()) {
                        $r .= '<div class="input-group-prepend"><span class="input-group-text">' . $atributoPai->getPrefixo() . '</span></div>';
                    }
                    $r .= '<input form="' . $form . '" type="number" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . ']" class="form-control" value="' . $valor . '"' . ($atributoPai->getEditavel() === 'N' ? ' readonly ' : '') . '>';
                    if ($atributoPai->getSufixo()) {
                        $r .= '<div class="input-group-append"><span class="input-group-text">' . $atributoPai->getSufixo() . '</span></div>';
                    }
                    $r .= '</div>';
                    return $r;
                case 'DECIMAL1':
                case 'DECIMAL2':
                case 'DECIMAL3':
                case 'DECIMAL4':
                case 'DECIMAL5':
                    $valFormatado = number_format((float)$valor, $tipo[7], ',', '.');
                    $r = '<div class="input-group">';
                    if ($atributoPai->getPrefixo()) {
                        $r .= '<div class="input-group-prepend"><span class="input-group-text">' . $atributoPai->getPrefixo() . '</span></div>';
                    }
                    $r .= '<input form="' . $form . '" type="text" class="form-control ' . strtolower($tipo) . '" id="' . $prefixo . '_' . $id . '_' . '" name="' . $prefixo . '[' . $id . ']" value="' . $valFormatado . '"' . ($atributoPai->getEditavel() === 'N' ? ' readonly ' : '') . '>';
                    if ($atributoPai->getSufixo()) {
                        $r .= '<div class="input-group-append"><span class="input-group-text">' . $atributoPai->getSufixo() . '</span></div>';
                    }
                    $r .= '</div>';
                    return $r;
                case 'DATE':
                    if ($valor) {
                        $dt = DateTimeUtils::parseDateStr($valor);
                        $valor = $dt ? $dt->format('d/m/Y') : null;
                    }
                    $r = '<div class="input-group">';
                    if ($atributoPai->getPrefixo()) {
                        $r .= '<div class="input-group-prepend"><span class="input-group-text">' . $atributoPai->getPrefixo() . '</span></div>';
                    }
                    $r .= '<input form="' . $form . '" type="text" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . ']" class="form-control crsr-' . strtolower($tipo) . '" value="' . $valor . '"' . ($atributoPai->getEditavel() === 'N' ? ' readonly ' : '') . '>';
                    if ($atributoPai->getSufixo()) {
                        $r .= '<div class="input-group-append"><span class="input-group-text">' . $atributoPai->getSufixo() . '</span></div>';
                    }
                    $r .= '</div>';
                    return $r;
                case 'DATETIME':
                    if ($valor) {
                        $dt = DateTimeUtils::parseDateStr($valor);
                        $valor = $dt ? $dt->format('d/m/Y H:i:s') : null;
                    }

                    $r = '<div class="input-group">';
                    if ($atributoPai->getPrefixo()) {
                        $r .= '<div class="input-group-prepend"><span class="input-group-text">' . $atributoPai->getPrefixo() . '</span></div>';
                    }
                    $r .= '<input form="' . $form . '" type="text" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . ']" class="form-control crsr-' . strtolower($tipo) . '" value="' . $valor . '"' . ($atributoPai->getEditavel() === 'N' ? ' readonly ' : '') . '>';
                    if ($atributoPai->getSufixo()) {
                        $r .= '<div class="input-group-append"><span class="input-group-text">' . $atributoPai->getSufixo() . '</span></div>';
                    }
                    $r .= '</div>';
                    return $r;
                case 'LISTA':

                    $subatributos = $repoAtributo->findBy(['paiUUID' => $atributoPai->getUUID()], ['label' => 'ASC']);

                    $atributosOptionsSelect2 = Select2JsUtils::toSelect2DataFn($subatributos, function ($e) {
                        /** @var Atributo $e */
                        return $e->getDescricao();
                    }, [$valor]);
                    array_unshift($atributosOptionsSelect2, ['id' => -1, 'text' => 'Selecione...']);
                    $atributosOptions = json_encode($atributosOptionsSelect2);

                    return '<select form="' . $form . '" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . ']"
                                            data-options="' . htmlentities($atributosOptions) . '"
                                            class="form-control autoSelect2"></select>';
                case 'TAGS':

//                    $tagsoptions = null;
//                    $options = [];
//                    if ($valor) {
//                        $selecteds = explode(',', $valor);
//
//                        foreach ($selecteds as $val) {
//                            $options[] = ['id' => $val, 'text' => $val, 'selected' => true];
//                        }
//                    }
//                    $tagsoptions = htmlentities(json_encode($options));

                    return '<select multiple form="' . $form . '" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . '][]"
                                data-tagsoptions="' . $valor . '"
                                class="form-control autoSelect2 notuppercase"></select>';
                case 'S/N':

                    $atributosOptionsSelect2 = [
                        ['id' => 'S', 'text' => 'SIM'],
                        ['id' => 'N', 'text' => 'NÃO'],
                    ];
                    array_unshift($atributosOptionsSelect2, ['id' => '', 'text' => 'Selecione...']);
                    $atributosOptions = json_encode($atributosOptionsSelect2);

                    return '<select form="' . $form . '" id="' . $prefixo . '_' . $id . '" name="' . $prefixo . '[' . $id . ']" data-val="' . $valor . '"
                                            data-options="' . htmlentities($atributosOptions) . '"
                                            class="form-control autoSelect2"></select>';
                case 'COMPO':
                    $campos = explode('|', $atributoPai->getConfig());
                    $valores = explode('|', $valor);
                    $r = '<div class="form-inline d-flex flex-nowrap ">';
                    $i = 0;
                    foreach ($campos as $campo) {
                        $val = $valores[$i] ?? '';
                        list($prefixoCampo, $classes, $sufixo) = explode(',', $campo);

                        $r .= '<div class="input-group mr-2">';
                        if ($prefixoCampo) {
                            $r .= '<div class="input-group-prepend"><span class="input-group-text">' . $prefixoCampo . '</span></div>';
                        }
                        $r .= '<input form="' . $form . '" type="text" class="form-control ' . $classes . '" id="' . $prefixo . '_' . $id . '_' . $i . '" name="' . $prefixo . '[' . $id . '][]" value="' . $val . '">';
                        if ($sufixo) {
                            $r .= '<div class="input-group-append"><span class="input-group-text">' . $sufixo . '</span></div>';
                        }
                        $r .= '</div>';

                        $i++;
                    }
                    $r .= '</div>';
                    return $r;

                    break;

                default:
                    return 'tipo não definido';
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao construir campo [ buildInput($prefixo=' . $prefixo . ', $id=' . $id . ',$tipo=' . $tipo . ',$valor=' . $valor . ') ]');
            return '<< erro ao construir campo >>';
        }

    }

}