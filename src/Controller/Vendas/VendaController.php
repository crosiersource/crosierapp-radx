<?php

namespace App\Controller\Vendas;

use CrosierSource\CrosierLibBaseBundle\Controller\BaseController;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\NumberUtils\DecimalUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ViewUtils\Select2JsUtils;
use CrosierSource\CrosierLibRadxBundle\Entity\CRM\Cliente;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\Repository\CRM\ClienteRepository;
use CrosierSource\CrosierLibRadxBundle\Repository\Vendas\VendaRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @package Cliente\Controller\Crediario
 * @author Carlos Eduardo Pauluk
 */
class VendaController extends BaseController
{

    private Pdf $knpSnappyPdf;


    /**
     * @required
     * @param Pdf $knpSnappyPdf
     */
    public function setKnpSnappyPdf(Pdf $knpSnappyPdf): void
    {
        $this->knpSnappyPdf = $knpSnappyPdf;
    }

    /**
     *
     * @Route("/crd/venda/form", name="crd_venda_form")
     * @param Request $request
     * @return Response
     *
     * @IsGranted("ROLE_CREDIARIO", statusCode=403)
     * @throws \Exception
     */
    public function formVenda(Request $request): Response
    {
        $params = [];

        $venda = $request->get('venda');
        if ($venda ?? false) {

            $clienteId = $venda['cliente'];
            /** @var ClienteRepository $repoCliente */
            $repoCliente = $this->getDoctrine()->getRepository(Cliente::class);
            /** @var Cliente $cliente */
            $cliente = $repoCliente->find($clienteId);


            $params['clienteOptions'] = json_encode(
                Select2JsUtils::toSelect2DataFn([$cliente],
                    function ($e) {
                        /** @var Cliente $e */
                        return $e->nome;
                    },
                    $clienteId));


            $numParcelas = $venda['numParcelas'];

            $valorTotal = DecimalUtils::parseStr($venda['valor']);
            $dtPrimeira = DateTimeUtils::parseDateStr($venda['dtPrimeira']);

            $parcelas = $this->vendaBusiness->gerarParcelas($valorTotal, $numParcelas, $dtPrimeira);

            $params['parcelas'] = $parcelas;

            if ($venda['pvs'] ?? false) {
                $venda['pvs'] = implode(',', $venda['pvs']);
            }

            $params['venda'] = $venda;

            if ($request->get('btnSalvar')) {
                try {
                    $venda = $this->vendaBusiness->salvarVenda($params);
                    return $this->redirectToRoute('crd_venda_visualiz', ['venda' => $venda->getId()]);
                } catch (ViewException $e) {
                    $this->addFlash('error', $e->getMessage());
                    $this->getLogger()->error($e->getMessage());
                } catch (\Throwable $e) {
                    $this->addFlash('error', 'Erro ao salvar venda');
                    $this->getLogger()->error($e->getMessage());
                }
            }
        } else {
            $dtPrimeira = new \DateTime();
            $dtPrimeira->setDate($dtPrimeira->format('Y'), (int)$dtPrimeira->format('m') + 1, $dtPrimeira->format('d'))->setTime(0, 0);
            $venda['dtPrimeira'] = $dtPrimeira->format('d/m/Y');
            $params['venda'] = $venda;
        }
        return $this->doRender('/Crediario/venda.html.twig', $params);
    }

    /**
     *
     * @Route("/crd/venda/visualiz/{venda}", name="crd_venda_visualiz", requirements={"venda"="\d+"})
     * @param Venda $venda
     * @return Response
     *
     * @IsGranted("ROLE_CREDIARIO", statusCode=403)
     */
    public function visualizVenda(Venda $venda): Response
    {
        $params = [];
        $params['venda'] = $venda;
        return $this->doRender('/Crediario/venda_visualiz.html.twig', $params);
    }

    /**
     *
     * @Route("/crd/venda/carnePDF/{venda}", name="crd_venda_carnePDF", requirements={"venda"="\d+"})
     * @ParamConverter("venda", class="App\Entity\Crediario\Venda", options={"id" = "venda"})
     * @param Venda $venda
     *
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_CREDIARIO", statusCode=403)
     */
    public function carnePDF(Venda $venda): Response
    {
        $params['venda'] = $venda;
        $params['agora'] = new \DateTime();
        $html = $this->renderView('/Crediario/PDF/carne.html.twig', $params);

        $this->knpSnappyPdf->setOption('page-width', '8cm');
        $this->knpSnappyPdf->setOption('page-height', '32cm');

        return new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html),
            'carne.pdf', 'application/pdf', 'inline'
        );

    }

    /**
     *
     * @Route("/crd/venda/carneHTML/{venda}", name="crd_venda_carneHTML", requirements={"venda"="\d+"})
     * @ParamConverter("venda", class="App\Entity\Crediario\Venda", options={"id" = "venda"})
     * @param Venda $venda
     *
     * @return Response
     * @throws \Exception
     * @IsGranted("ROLE_CREDIARIO", statusCode=403)
     */
    public function carneHTML(Venda $venda): Response
    {
        $params['venda'] = $venda;
        $params['agora'] = new \DateTime();
        $html = $this->renderView('/Crediario/PDF/carne.html.twig', $params);

        return $this->render('/Crediario/PDF/carne.html.twig', $params);

    }


    /**
     *
     * @Route("/ven/venda/listPorDia/{dia}", name="ven_venda_listPorDia")
     * @param \DateTime $dia
     * @return Response
     *
     * @throws \Exception
     * @IsGranted("ROLE_CREDIARIO", statusCode=403)
     */
    public function vendasPorDia(\DateTime $dia = null): Response
    {
        if (!$dia) {
            $dia = new \DateTime();
        }
        $params = [];

        /** @var VendaRepository $repoVenda */
        $repoVenda = $this->getDoctrine()->getRepository(Venda::class);
        $vendas = $repoVenda->findPorDia($dia);


        $params['vendas'] = $vendas;
        $params['dia'] = $dia->format('d/m/Y');

        $params['valorTotalVendasDia'] = $repoVenda->findTotalVendasDia($dia);
        $params['valorTotalVendasDia_semContrato'] = $repoVenda->findTotalVendasDia($dia, false);
        $params['valorTotalVendasDia_comContrato'] = $repoVenda->findTotalVendasDia($dia, true);


        return $this->doRender('/Crediario/vendas_list.html.twig', $params);
    }


}