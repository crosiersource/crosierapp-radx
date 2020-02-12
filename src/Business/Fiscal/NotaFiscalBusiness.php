<?php

namespace App\Business\Fiscal;

use App\Entity\Estoque\Produto;
use App\Entity\Fiscal\FinalidadeNF;
use App\Entity\Fiscal\IndicadorFormaPagto;
use App\Entity\Fiscal\NCM;
use App\Entity\Fiscal\NotaFiscal;
use App\Entity\Fiscal\NotaFiscalCartaCorrecao;
use App\Entity\Fiscal\NotaFiscalHistorico;
use App\Entity\Fiscal\NotaFiscalItem;
use App\Entity\Fiscal\NotaFiscalVenda;
use App\Entity\Fiscal\TipoNotaFiscal;
use App\Entity\Vendas\Venda;
use App\EntityHandler\Fiscal\NotaFiscalEntityHandler;
use App\EntityHandler\Fiscal\NotaFiscalHistoricoEntityHandler;
use App\EntityHandler\Fiscal\NotaFiscalItemEntityHandler;
use App\EntityHandler\Fiscal\NotaFiscalVendaEntityHandler;
use App\Repository\Fiscal\NCMRepository;
use App\Repository\Fiscal\NotaFiscalRepository;
use App\Repository\Fiscal\NotaFiscalVendaRepository;
use App\Utils\Fiscal\NFeUtils;
use CrosierSource\CrosierLibBaseBundle\APIClient\CrosierEntityIdAPIClient;
use CrosierSource\CrosierLibBaseBundle\Business\BaseBusiness;
use CrosierSource\CrosierLibBaseBundle\Entity\Base\Municipio;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Repository\Base\MunicipioRepository;
use CrosierSource\CrosierLibBaseBundle\Utils\StringUtils\StringUtils;
use Doctrine\DBAL\Connection;

/**
 *
 * @package App\Business\Fiscal
 * @author Carlos Eduardo Pauluk
 */
class NotaFiscalBusiness extends BaseBusiness
{

    private Connection $conn;

    /** @var SpedNFeBusiness */
    private $spedNFeBusiness;

    /** @var NotaFiscalEntityHandler */
    private $notaFiscalEntityHandler;

    /** @var NotaFiscalItemEntityHandler */
    private $notaFiscalItemEntityHandler;

    /** @var NotaFiscalVendaEntityHandler */
    private $notaFiscalVendaEntityHandler;

    /** @var NotaFiscalHistoricoEntityHandler */
    private $notaFiscalHistoricoEntityHandler;

    /** @var CrosierEntityIdAPIClient */
    private $crosierEntityIdAPIClient;

    /** @var NFeUtils */
    private $nfeUtils;

    /**
     * Não podemos usar o doctrine->getRepository porque ele não injeta as depêndencias que estão com @ required lá
     * @var NotaFiscalRepository
     */
    private $repoNotaFiscal;

    /**
     * @required
     */
    public function setConn(Connection $conn): void
    {
        $this->conn = $conn;
    }

    /**
     * @required
     * @param SpedNFeBusiness $spedNFeBusiness
     */
    public function setSpedNFeBusiness(SpedNFeBusiness $spedNFeBusiness): void
    {
        $this->spedNFeBusiness = $spedNFeBusiness;
    }

    /**
     * @required
     * @param NotaFiscalEntityHandler $notaFiscalEntityHandler
     */
    public function setNotaFiscalEntityHandler(NotaFiscalEntityHandler $notaFiscalEntityHandler): void
    {
        $this->notaFiscalEntityHandler = $notaFiscalEntityHandler;
    }

    /**
     * @required
     * @param NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler
     */
    public function setNotaFiscalItemEntityHandler(NotaFiscalItemEntityHandler $notaFiscalItemEntityHandler): void
    {
        $this->notaFiscalItemEntityHandler = $notaFiscalItemEntityHandler;
    }

    /**
     * @required
     * @param NotaFiscalVendaEntityHandler $notaFiscalVendaEntityHandler
     */
    public function setNotaFiscalVendaEntityHandler(NotaFiscalVendaEntityHandler $notaFiscalVendaEntityHandler): void
    {
        $this->notaFiscalVendaEntityHandler = $notaFiscalVendaEntityHandler;
    }

    /**
     * @required
     * @param NotaFiscalHistoricoEntityHandler $notaFiscalHistoricoEntityHandler
     */
    public function setNotaFiscalHistoricoEntityHandler(NotaFiscalHistoricoEntityHandler $notaFiscalHistoricoEntityHandler): void
    {
        $this->notaFiscalHistoricoEntityHandler = $notaFiscalHistoricoEntityHandler;
    }

    /**
     * @required
     * @param CrosierEntityIdAPIClient $crosierEntityIdAPIClient
     */
    public function setGenericAPIClient(CrosierEntityIdAPIClient $crosierEntityIdAPIClient): void
    {
        $this->crosierEntityIdAPIClient = $crosierEntityIdAPIClient;
    }

    /**
     * @required
     * @param NFeUtils $nfeUtils
     */
    public function setNfeUtils(NFeUtils $nfeUtils): void
    {
        $this->nfeUtils = $nfeUtils;
    }

    /**
     * @required
     * @param NotaFiscalRepository $repoNotaFiscal
     */
    public function setRepoNotaFiscal(NotaFiscalRepository $repoNotaFiscal): void
    {
        $this->repoNotaFiscal = $repoNotaFiscal;
    }


    /**
     * Verifica se está acessando o arquivo controle.txt para evitar trabalhar com diretório desmontado.
     * @return bool
     */
    public function checkAcessoPVs(): bool
    {
        $dir = $_SERVER['PASTAARQUIVOSEKTFISCAL'];
        $files = scandir($dir, SCANDIR_SORT_NONE);
        return in_array('controle.txt', $files, true) ? true : false;
    }

    /**
     * Transforma um ven_venda em um fis_nf.
     *
     * @param Venda $venda
     * @param NotaFiscal $notaFiscal
     * @param bool $alterouTipo
     * @return null|NotaFiscal
     */
    public function saveNotaFiscalVenda(Venda $venda, NotaFiscal $notaFiscal, bool $alterouTipo): ?NotaFiscal
    {
        try {
            $this->getDoctrine()->beginTransaction();

            /** @var NotaFiscalVendaRepository $repoNotaFiscalVenda */
            $repoNotaFiscalVenda = $this->getDoctrine()->getRepository(NotaFiscalVenda::class);
            /** @var NotaFiscal $jaExiste */
            $jaExiste = $repoNotaFiscalVenda->findNotaFiscalByVenda($venda);
            if ($jaExiste) {
                $notaFiscal = $jaExiste;
                $novaNota = false;
            } else {
                $novaNota = true;
            }

            $notaFiscal->setEntradaSaida('S');

            $nfeConfigs = $this->nfeUtils->getNFeConfigsEmUso();

            $notaFiscal->setDocumentoEmitente($nfeConfigs['cnpj']);
            $notaFiscal->setXNomeEmitente($nfeConfigs['razaosocial']);
            $notaFiscal->setInscricaoEstadualEmitente($nfeConfigs['ie']);

            $notaFiscal->setNaturezaOperacao('VENDA');

            $dtEmissao = new \DateTime();
            $dtEmissao->modify(' - 4 minutes');
            $notaFiscal->setDtEmissao($dtEmissao);
            $notaFiscal->setDtSaiEnt($dtEmissao);

            $notaFiscal->setFinalidadeNf(FinalidadeNF::NORMAL['key']);

            if ($alterouTipo) {
                $notaFiscal->setDtEmissao(null);
                $notaFiscal->setNumero(null);
                $notaFiscal->setCnf(null);
                $notaFiscal->setChaveAcesso(null);
            }

            // Aqui somente coisas que fazem sentido serem alteradas depois de já ter sido (provavelmente) tentado o faturamento da Notafiscal.
            $notaFiscal->setTranspModalidadeFrete('SEM_FRETE');

            $notaFiscal->setIndicadorFormaPagto(
                $venda->getPlanoPagto()->getCodigo() === '1.00' ? IndicadorFormaPagto::VISTA['codigo'] : IndicadorFormaPagto::PRAZO['codigo']);

            $notaFiscal = $this->notaFiscalEntityHandler->deleteAllItens($notaFiscal);
            $this->getDoctrine()->flush();

            // Atenção, aqui tem que verificar a questão do arredondamento
            if ($venda->getSubTotal() > 0.0) {
                $fatorDesconto = 1 - round(bcdiv($venda->getValorTotal(), $venda->getSubTotal(), 4), 2);
            } else {
                $fatorDesconto = 1;
            }

            $somaDescontosItens = 0.0;
            $ordem = 1;
            foreach ($venda->getItens() as $vendaItem) {

                $nfItem = new NotaFiscalItem();
                $nfItem->setNotaFiscal($notaFiscal);

                if ($vendaItem->getNcm()) {
                    /** @var NCMRepository $repoNCM */
                    $repoNCM = $this->getDoctrine()->getRepository(NCM::class);
                    $existe = $repoNCM->findBy(['codigo' => $vendaItem->getNcm()]);
                    if (!$existe) {
                        $nfItem->setNcm('62179000'); // FIXME: RTA
                    } else {
                        $nfItem->setNcm($vendaItem->getNcm());
                    }
                } else {
                    $nfItem->setNcm('62179000'); // FIXME: RTA
                }


                $nfItem->setOrdem($ordem++);

                $nfItem->setQtde($vendaItem->getQtde());
                $nfItem->setValorUnit($vendaItem->getPrecoVenda());
                $nfItem->setValorTotal($vendaItem->getTotalItem());

                $vDesconto = round(bcmul($vendaItem->getTotalItem(), $fatorDesconto, 4), 2);
                $nfItem->setValorDesconto($vDesconto);

                // Somando aqui pra verificar depois se o total dos descontos dos itens bate com o desconto global da nota.
                $somaDescontosItens += $vDesconto;

                $nfItem->setSubTotal($vendaItem->getTotalItem());

                $nfItem->setIcmsAliquota(0.0);
                $nfItem->setCfop('5102');
                if ($vendaItem->getProduto() && $vendaItem->getProduto()->getUnidade() && $vendaItem->getProduto()->getUnidade()->getLabel()) {
                    $nfItem->setUnidade($vendaItem->getProduto()->getUnidade()->getLabel());
                } else {
                    $nfItem->setUnidade('PC');
                }

                if ($vendaItem->getProduto() !== null) {
                    $produto = $this->getDoctrine()->getRepository(Produto::class)->findOneBy(['id' => $vendaItem->getProduto()->getId()]);
                    $nfItem->setCodigo($vendaItem->getProduto()->getId());
                    $nfItem->setDescricao(trim($vendaItem->getProduto()->getNome()));
                } else {
                    $nfItem->setCodigo($vendaItem->getNcReduzido()); // FIXME: melhorar
                    $nfItem->setDescricao(trim($vendaItem->getNcDescricao()));
                }

                $this->notaFiscalEntityHandler->handleSavingEntityId($nfItem);
                $notaFiscal->addItem($nfItem);
            }

            $this->calcularTotais($notaFiscal);
            $totalDescontos = bcsub($notaFiscal->getSubTotal(), $notaFiscal->getValorTotal(), 2);

            if ((float)bcsub(abs($totalDescontos), abs($somaDescontosItens), 2) !== 0.0) {
                $diferenca = $totalDescontos - $somaDescontosItens;
                $notaFiscal->getItens()
                    ->get(0)
                    ->setValorDesconto($notaFiscal->getItens()
                            ->get(0)
                            ->getValorDesconto() + $diferenca);
                $notaFiscal->getItens()
                    ->get(0)
                    ->calculaTotais();
            }

            /** @var NotaFiscal $notaFiscal */
            $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);
            $this->getDoctrine()->flush();

            if ($novaNota) {
                $notaFiscalVenda = new NotaFiscalVenda();
                $notaFiscalVenda->setNotaFiscal($notaFiscal);
                $notaFiscalVenda->setVenda($venda);
                $this->notaFiscalVendaEntityHandler->save($notaFiscalVenda);
            }

            $this->getDoctrine()->commit();
            return $notaFiscal;
        } catch (\Exception $e) {
            $this->getDoctrine()->rollback();
            $erro = 'Erro ao gerar registro da Nota Fiscal';
            throw new \RuntimeException($erro, null, $e);
        }
    }

    /**
     * Lida com os campos que são gerados programaticamente.
     *
     * @param $notaFiscal
     * @return bool
     * @throws ViewException
     */
    public function handleIdeFields(NotaFiscal $notaFiscal): bool
    {
        try {
            $mudou = false;
            if (!$notaFiscal->getUuid()) {
                $notaFiscal->setUuid(md5(uniqid(mt_rand(), true)));
                $mudou = true;
            }
            if (!$notaFiscal->getCnf()) {
                $cNF = random_int(10000000, 99999999);
                $notaFiscal->setCnf($cNF);
                $mudou = true;
            }// Rejeição 539: Duplicidade de NF-e, com diferença na Chave de Acesso
            if (!$notaFiscal->getNumero() || $notaFiscal->getCStat() === 539) {
                $nfeConfigs = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->getDocumentoEmitente());

                $ambiente = $nfeConfigs['tpAmb'] === 1 ? 'PROD' : 'HOM';
                $notaFiscal->setAmbiente($ambiente);

                if (!$notaFiscal->getTipoNotaFiscal()) {
                    throw new \Exception('Impossível gerar número sem saber o tipo da nota fiscal.');
                }
                $chaveSerie = 'serie_' . $notaFiscal->getTipoNotaFiscal() . '_' . $ambiente;
                $serie = $nfeConfigs[$chaveSerie];
                if (!$serie) {
                    throw new ViewException('Série não encontrada para ' . $chaveSerie);
                }
                $notaFiscal->setSerie($serie);

                /** @var NotaFiscalRepository $repoNotaFiscal */
                $nnf = $this->repoNotaFiscal->findProxNumFiscal($ambiente, $notaFiscal->getSerie(), $notaFiscal->getTipoNotaFiscal());
                $notaFiscal->setNumero($nnf);
                $mudou = true;
            }
            if (!$notaFiscal->getDtEmissao()) {
                $notaFiscal->setDtEmissao(new \DateTime());
                $mudou = true;
            }
            if ($mudou || !$notaFiscal->getChaveAcesso() || !preg_match('/[0-9]{44}/', $notaFiscal->getChaveAcesso())) {
                $notaFiscal->setChaveAcesso($this->buildChaveAcesso($notaFiscal));
                $mudou = true;
            }
            if ($mudou) {
                $this->notaFiscalEntityHandler->save($notaFiscal);
            }
            return $mudou;
        } catch (\Throwable $e) {
            $this->getLogger()->error('handleIdeFields');
            $this->getLogger()->error($e->getMessage());
            throw new ViewException('Erro ao gerar campos ide');
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return string
     * @throws ViewException
     */
    public function buildChaveAcesso(NotaFiscal $notaFiscal)
    {
        $nfeConfigs = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->getDocumentoEmitente());
        $cUF = '41';

        $cnpj = $nfeConfigs['cnpj'];
        $ano = $notaFiscal->getDtEmissao()->format('y');
        $mes = $notaFiscal->getDtEmissao()->format('m');
        $mod = TipoNotaFiscal::get($notaFiscal->getTipoNotaFiscal())['codigo'];
        $serie = $notaFiscal->getSerie();
        $nNF = $notaFiscal->getNumero();
        $cNF = $notaFiscal->getCnf();

        // Campo tpEmis
        // 1-Emissão Normal
        // 2-Contingência em Formulário de Segurança
        // 3-Contingência SCAN (desativado)
        // 4-Contingência EPEC
        // 5-Contingência em Formulário de Segurança FS-DA
        // 6-Contingência SVC-AN
        // 7-Contingência SVC-RS
        $tpEmis = 1;

        $chaveAcesso = NFeKeys::build($cUF, $ano, $mes, $cnpj, $mod, $serie, $nNF, $tpEmis, $cNF);
        return $chaveAcesso;
    }

    /**
     * Calcula o total da nota e o total de descontos.
     *
     * @param
     *            nf
     */
    public function calcularTotais(NotaFiscal $notaFiscal): void
    {
        $subTotal = 0.0;
        $descontos = 0.0;
        foreach ($notaFiscal->getItens() as $item) {
            $item->calculaTotais();
            $subTotal += $item->getSubTotal();
            $descontos += $item->getValorDesconto() ? $item->getValorDesconto() : 0.0;
        }
        $notaFiscal->setSubTotal($subTotal);
        $notaFiscal->setTotalDescontos($descontos);
        $notaFiscal->setValorTotal($subTotal - $descontos);
    }

    /**
     * Salvar uma notaFiscal normal.
     *
     * @param
     *            $tipoNotaFiscal
     * @return NULL|\App\Entity\Fiscal\NotaFiscal
     * @throws \Exception
     */
    public function saveNotaFiscal(NotaFiscal $notaFiscal): ?NotaFiscal
    {
        try {
            if (!$notaFiscal->getTipoNotaFiscal()) {
                throw new ViewException('Tipo da Nota não informado');
            }
            $this->getDoctrine()->beginTransaction();

            $nfeConfigs = $this->nfeUtils->getNFeConfigsByCNPJ($notaFiscal->getDocumentoEmitente());

            $notaFiscal->setXNomeEmitente($nfeConfigs['razaosocial']);
            $notaFiscal->setInscricaoEstadualEmitente($nfeConfigs['ie']);

            if (!$notaFiscal->getUuid()) {
                $notaFiscal->setUuid(md5(uniqid(mt_rand(), true)));
            }

            if (!$notaFiscal->getSerie()) {
                $notaFiscal->setSerie($notaFiscal->getTipoNotaFiscal() === 'NFE' ? $nfeConfigs['serieNFe'] : $nfeConfigs['serieNFCe']);
            }

            if (!$notaFiscal->getCnf()) {
                $cNF = random_int(10000000, 99999999);
                $notaFiscal->setCnf($cNF);
            }

            $this->calcularTotais($notaFiscal);
            $this->notaFiscalEntityHandler->save($notaFiscal);
            $this->getDoctrine()->commit();
            return $notaFiscal;
        } catch (\Exception $e) {
            $this->getDoctrine()->rollback();
            $erro = 'Erro ao salvar Nota Fiscal';
            throw new ViewException($erro, null, $e);
        }
    }

    /**
     * Corrige os NCMs. Na verdade troca para um NCM genérico nos casos onde o NCM informado não exista na base.
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws \Exception
     */
    public function corrigirNCMs(NotaFiscal $notaFiscal): NotaFiscal
    {
        $this->getDoctrine()->refresh($notaFiscal);
        if ($notaFiscal->getItens()) {
            foreach ($notaFiscal->getItens() as $item) {
                /** @var NCMRepository $repoNCM */
                $repoNCM = $this->getDoctrine()->getRepository(NCM::class);
                $existe = $repoNCM->findByNCM($item->getNcm());
                if (!$existe) {
                    $item->setNcm('62179000');
                }
            }
        }
        $this->getDoctrine()->flush();
        return $notaFiscal;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws \Exception
     */
    public function faturarNFe(NotaFiscal $notaFiscal): NotaFiscal
    {
        // Verifica algumas regras antes de mandar faturar na receita.
        $this->checkNotaFiscal($notaFiscal);

        $this->addHistorico($notaFiscal, -1, 'INICIANDO FATURAMENTO');
        if ($this->permiteFaturamento($notaFiscal)) {
            if ($notaFiscal->getNRec()) {
                $this->spedNFeBusiness->consultaRecibo($notaFiscal);
                if ($notaFiscal->getCStat() === 502) {
                    $notaFiscal->setChaveAcesso(null); // será regerada no handleIdeFields()
                }
            }
            $this->handleIdeFields($notaFiscal);
            $notaFiscal = $this->spedNFeBusiness->gerarXML($notaFiscal);

            $notaFiscal = $this->spedNFeBusiness->enviaNFe($notaFiscal);
            if ($notaFiscal) {
                $this->addHistorico($notaFiscal, $notaFiscal->getCStat() ?: -1, $notaFiscal->getXMotivo(), 'FATURAMENTO PROCESSADO');
                // $this->imprimir($notaFiscal);
            } else {
                $this->addHistorico($notaFiscal, -2, 'PROBLEMA AO FATURAR');
            }
        } else {
            $this->addHistorico($notaFiscal, 0, 'NOTA FISCAL NÃO FATURÁVEL. STATUS = [' . $notaFiscal->getCStat() . ']');
        }

        return $notaFiscal;
    }

    /**
     *
     * @param NotaFiscal $notaFiscal
     * @throws \Exception
     */
    public function checkNotaFiscal(NotaFiscal $notaFiscal): void
    {
        if (!$notaFiscal) {
            throw new \RuntimeException('Nota Fiscal null');
        }
        if ($notaFiscal->getCidadeDestinatario()) {

            /** @var MunicipioRepository $repoMunicipio */
            $repoMunicipio = $this->getDoctrine()->getRepository(Municipio::class);

            /** @var Municipio $r */
            $r = $repoMunicipio->findOneByFiltersSimpl([
                ['municipioNome', 'EQ', $notaFiscal->getCidadeDestinatario()],
                ['ufSigla', 'EQ', $notaFiscal->getEstadoDestinatario()]
            ]);


            if (!$r || strtoupper(StringUtils::removerAcentos($r->getMunicipioNome())) !== strtoupper(StringUtils::removerAcentos($notaFiscal->getCidadeDestinatario()))) {
                throw new ViewException('Município inválido: [' . $notaFiscal->getCidadeDestinatario() . '-' . $notaFiscal->getEstadoDestinatario() . ']');
            }
        }

        if ($notaFiscal->getDtEmissao() > $notaFiscal->getDtSaiEnt()) {
            throw new ViewException('Dt Emissão maior que Dt Saída/Entrada. Não é possível faturar.');
        }

    }

    /**
     * @param NotaFiscal $notaFiscal
     * @param $codigoStatus
     * @param $descricao
     * @param null $obs
     * @throws ViewException
     */
    public function addHistorico(NotaFiscal $notaFiscal, $codigoStatus, $descricao, $obs = null): void
    {
        $historico = new NotaFiscalHistorico();
        $dtHistorico = new \DateTime();
        $historico->setDtHistorico($dtHistorico);
        $historico->setCodigoStatus($codigoStatus);
        $historico->setDescricao($descricao ? $descricao : ' ');
        $historico->setObs($obs);
        $historico->setNotaFiscal($notaFiscal);
        $this->notaFiscalHistoricoEntityHandler->save($historico);
    }

    /**
     * Só exibe o botão faturar se tiver nestas condições.
     * Lembrando que o botão "Faturar" serve tanto para faturar a primeira vez, como para tentar faturar novamente nos casos de erros.
     *
     * @param
     *            venda
     * @return
     */
    public function permiteFaturamento(NotaFiscal $notaFiscal): bool
    {
        if ($notaFiscal && $notaFiscal->getId() && in_array($notaFiscal->getCStat(), [-100, 100, 101, 204, 135], false)) {
            return false;
        }
        if ($notaFiscal && !$notaFiscal->getId()) {
            return false;
        }
        return true;

    }

    /**
     * Só exibe o botão faturar se tiver nestas condições.
     * Lembrando que o botão "Faturar" serve tanto para faturar a primeira vez, como para tentar faturar novamente nos casos de erros.
     *
     * @param
     *            venda
     * @return
     */
    public function permiteSalvar(NotaFiscal $notaFiscal)
    {
        if (!$notaFiscal->getId() || $this->permiteFaturamento($notaFiscal)) {
            return true;
        }
        return false;

    }

    /**
     * Por enquanto o 'cancelar' segue a mesma regra do 'reimprimir'.
     *
     * @param NotaFiscal $notaFiscal
     * @return bool
     */
    public function permiteCancelamento(NotaFiscal $notaFiscal): ?bool
    {
        return (int)$notaFiscal->getCStat() === 100;
    }

    /**
     * Verifica se é possível reimprimir.
     *
     * @param NotaFiscal $notaFiscal
     * @return boolean
     */
    public function permiteReimpressao(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->getId()) {
            if ($notaFiscal->getCStat() == 100 || $notaFiscal->getCStat() == 204 || $notaFiscal->getCStat() == 135) {
                return true;
            }
            // else
            if ($notaFiscal->getCStat() == 0 && strpos($notaFiscal->getXMotivo(), 'DUPLICIDADE DE NF') !== FALSE) {
                return true;
            }

        }
        return false;
    }

    /**
     * Verifica se é possível reimprimir o cancelamento.
     *
     * @param NotaFiscal $notaFiscal
     * @return boolean
     */
    public function permiteReimpressaoCancelamento(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->getId()) {
            if ($notaFiscal->getCStat() == 101) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se é possível enviar carta de correção.
     *
     * @param NotaFiscal $notaFiscal
     * @return boolean
     */
    public function permiteCartaCorrecao(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->getId()) {
            if ($notaFiscal->getCStat() == 100) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param NotaFiscal $notaFiscal
     */
    public function imprimir(NotaFiscal $notaFiscal)
    {
        $this->spedNFeBusiness->imprimir($notaFiscal);
    }

    /**
     * @param NotaFiscal $notaFiscal
     */
    public function imprimirCancelamento(NotaFiscal $notaFiscal)
    {
        $this->spedNFeBusiness->imprimirCancelamento($notaFiscal);
    }

    /**
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     */
    public function imprimirCartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao)
    {
        $this->spedNFeBusiness->imprimirCartaCorrecao($cartaCorrecao);
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal|\CrosierSource\CrosierLibBaseBundle\Entity\EntityId|object
     * @throws ViewException
     */
    public function cancelar(NotaFiscal $notaFiscal)
    {
        $this->addHistorico($notaFiscal, -1, 'INICIANDO CANCELAMENTO');
        $notaFiscal = $this->checkChaveAcesso($notaFiscal);
        try {
            $notaFiscalR = $this->spedNFeBusiness->cancelar($notaFiscal);
            if ($notaFiscalR) {
                $notaFiscal = $notaFiscalR;
                $this->addHistorico($notaFiscal, $notaFiscal->getCStat() ?: -1, $notaFiscal->getXMotivo(), 'CANCELAMENTO PROCESSADO');
                $notaFiscal = $this->consultarStatus($notaFiscal);
                $this->spedNFeBusiness->imprimirCancelamento($notaFiscal);
            } else {
                $this->addHistorico($notaFiscal, -2, 'PROBLEMA AO CANCELAR');
            }
        } catch (\Exception | ViewException $e) {
            $this->addHistorico($notaFiscal, -2, 'PROBLEMA AO CANCELAR: [' . $e->getMessage() . ']');
            if ($e instanceof ViewException) {
                $this->addHistorico($notaFiscal, -2, $e->getMessage());
            }
        }
        return $notaFiscal;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal|\CrosierSource\CrosierLibBaseBundle\Entity\EntityId|object
     * @throws ViewException
     */
    public function checkChaveAcesso(NotaFiscal $notaFiscal)
    {
        if (!$notaFiscal->getChaveAcesso()) {
            $notaFiscal->setChaveAcesso($this->buildChaveAcesso($notaFiscal));

            $notaFiscal = $this->notaFiscalEntityHandler->save($notaFiscal);
            $this->getDoctrine()->flush();
        }
        return $notaFiscal;
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @return NotaFiscal
     * @throws ViewException
     */
    public function consultarStatus(NotaFiscal $notaFiscal): NotaFiscal
    {
        $this->addHistorico($notaFiscal, -1, 'INICIANDO CONSULTA DE STATUS');
        try {
            $notaFiscal = $this->spedNFeBusiness->consultarStatus($notaFiscal);
            if ($notaFiscal) {
                $this->addHistorico($notaFiscal, $notaFiscal->getCStat() ?: -1, $notaFiscal->getXMotivo(), 'CONSULTA DE STATUS PROCESSADA');
            } else {
                $this->addHistorico($notaFiscal, -2, 'PROBLEMA AO CONSULTAR STATUS');
            }
        } catch (\Exception $e) {
            $this->addHistorico($notaFiscal, -2, 'PROBLEMA AO CONSULTAR STATUS: [' . $e->getMessage() . ']');
        }
        return $notaFiscal;
    }

    /**
     * @param NotaFiscalCartaCorrecao $cartaCorrecao
     * @return NotaFiscal|NotaFiscalCartaCorrecao
     * @throws ViewException
     */
    public function cartaCorrecao(NotaFiscalCartaCorrecao $cartaCorrecao)
    {
        $this->addHistorico($cartaCorrecao->getNotaFiscal(), -1, 'INICIANDO ENVIO DA CARTA DE CORREÇÃO');
        try {
            $cartaCorrecao = $this->spedNFeBusiness->cartaCorrecao($cartaCorrecao);
            if ($cartaCorrecao) {
                $this->addHistorico(
                    $cartaCorrecao->getNotaFiscal(),
                    $cartaCorrecao->getNotaFiscal()->getCStat(),
                    $cartaCorrecao->getNotaFiscal()->getXMotivo(),
                    'ENVIO DA CARTA DE CORREÇÃO PROCESSADO');
                $this->consultarStatus($cartaCorrecao->getNotaFiscal());
                // $this->spedNFeBusiness->imprimirCartaCorrecao($cartaCorrecao);
            } else {
                $this->addHistorico($cartaCorrecao->getNotaFiscal(), -2, 'PROBLEMA AO ENVIAR CARTA DE CORREÇÃO');
            }
        } catch (\Exception $e) {
            $this->addHistorico($cartaCorrecao->getNotaFiscal(), -2, 'PROBLEMA AO ENVIAR CARTA DE CORREÇÃO: [' . $e->getMessage() . ']');
        }
        return $cartaCorrecao;
    }

    /**
     * @param $cnpj
     * @return mixed
     * @throws \Exception
     */
    public function consultarCNPJ($cnpj)
    {
        $r = [];
        $infCons = $this->spedNFeBusiness->consultarCNPJ($cnpj);
        if ($infCons->cStat->__toString() === '259') {
            $r['xMotivo'] = $infCons->xMotivo->__toString();
        } else {
            $r['dados'] = [
                'CNPJ' => $infCons->infCad->CNPJ->__toString(),
                'IE' => $infCons->infCad->IE->__toString(),
                'razaoSocial' => $infCons->infCad->xNome->__toString(),
                'CNAE' => $infCons->infCad->CNAE->__toString(),
                'logradouro' => $infCons->infCad->ender->xLgr->__toString(),
                'numero' => $infCons->infCad->ender->nro->__toString(),
                'complemento' => $infCons->infCad->ender->xCpl->__toString(),
                'bairro' => $infCons->infCad->ender->xBairro->__toString(),
                'cidade' => $infCons->infCad->ender->xMun->__toString(),
                'UF' => $infCons->infCad->UF->__toString(),
                'CEP' => $infCons->infCad->ender->CEP->__toString(),
            ];
        }
        return $r;

    }


    /**
     * @param $mesano
     * @return bool|string
     * @throws \Exception
     */
    public function criarZip($mesano)
    {
        $mesano = str_replace(' - ', '', $mesano);
        $zip = new \ZipArchive();

        $pastaUnimake = $_SERVER['FISCAL_UNIMAKE_PASTAROOT'];
        $pastaXMLs = $pastaUnimake . '/enviado/Autorizados/' . $mesano;
        $pastaF = $_SERVER['PASTA_F'];

        $pastaNFEs = $pastaF . '/NOTAS FISCAIS/NFE/' . $mesano;
        $pastaNFCEs = $pastaF . '/NOTAS FISCAIS/NFCE/' . $mesano;
        $pastaCARTACORRs = $pastaF . '/NOTAS FISCAIS/CARTACORR/' . $mesano;

        $zipname = $pastaUnimake . '/backup/' . $mesano . '.zip';

        if ($zip->open($zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception("cannot open <$zipname>");
        }

        $this->criarZipDir($zip, $pastaXMLs, 'xmls');
        $this->criarZipDir($zip, $pastaCARTACORRs, 'cartacorr');
        $this->criarZipDir($zip, $pastaNFCEs, 'nfce');
        $this->criarZipDir($zip, $pastaNFEs, 'nfe');

        // Zip archive will be created only after closing object
        $zip->close();
        return file_get_contents($zipname);

    }

    /**
     * @param \ZipArchive $zip
     * @param $pasta
     * @param $nomePasta
     */
    private function criarZipDir(\ZipArchive $zip, $pasta, $nomePasta)
    {
        $xmls = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pasta), \RecursiveIteratorIterator::LEAVES_ONLY);
        $zip->addEmptyDir($nomePasta);
        foreach ($xmls as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real && relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($pasta) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $nomePasta . '/' . $relativePath);
            }
        }
    }

    /**
     * @param NotaFiscal $notaFiscal
     * @param NotaFiscalItem $notaFiscalItem
     * @throws ViewException
     */
    public function colarItem(NotaFiscal $notaFiscal, NotaFiscalItem $notaFiscalItem)
    {
        /** @var NotaFiscalItem $novoItem */
        $novoItem = clone $notaFiscalItem;
        $novoItem->setId(null);
        $novoItem->setNotaFiscal($notaFiscal);
        $novoItem->setCodigo('?????');
        $novoItem->setOrdem(null);
        $this->notaFiscalItemEntityHandler->save($novoItem);
    }


    /**
     *
     * @return array obtido a partir das cfg_app_config de nfeConfigs_%
     */
    public function getEmitentes()
    {
        $nfeConfigs = $this->conn->fetchAll('SELECT * FROM cfg_app_config WHERE chave LIKE \'nfeConfigs\\_%\'');
        $emitentes = [];
        foreach ($nfeConfigs as $nfeConfig) {
            $dados = json_decode($nfeConfig['valor'], true);
            $emitentes[] = ['cnpj' => $dados['cnpj'], 'razaosocial' => $dados['razaosocial']];
        }
        return $emitentes;
    }

}
