<?php

namespace App\Business\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\EntityHandler\Config\PushMessageEntityHandler;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibBaseBundle\Utils\DateTimeUtils\DateTimeUtils;
use CrosierSource\CrosierLibBaseBundle\Utils\ExceptionUtils\ExceptionUtils;
use App\Entity\Ecommerce\ClienteConfig;
use App\Entity\Ecommerce\MercadoLivreItem;
use App\Entity\Ecommerce\MercadoLivrePergunta;
use App\EntityHandler\Ecommerce\ClienteConfigEntityHandler;
use App\EntityHandler\Ecommerce\MercadoLivreItemEntityHandler;
use App\EntityHandler\Ecommerce\MercadoLivrePerguntaEntityHandler;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Carlos Eduardo Pauluk
 */
class MercadoLivreBusiness
{

    private ClienteConfigEntityHandler $clienteConfigEntityHandler;

    private MercadoLivrePerguntaEntityHandler $mercadoLivrePerguntaEntityHandler;

    private MercadoLivreItemEntityHandler $mercadoLivreItemEntityHandler;

    private IntegradorMercadoLivre $integradorMercadoLivre;

    private SyslogBusiness $syslog;

    private PushMessageEntityHandler $pushMessageEntityHandler;


    public function __construct(ClienteConfigEntityHandler        $clienteConfigEntityHandler,
                                MercadoLivrePerguntaEntityHandler $mercadoLivrePerguntaEntityHandler,
                                MercadoLivreItemEntityHandler     $mercadoLivreItemEntityHandler,
                                IntegradorMercadoLivre            $integradorMercadoLivre,
                                SyslogBusiness                    $syslog,
                                PushMessageEntityHandler          $pushMessageEntityHandler
    )
    {
        $this->clienteConfigEntityHandler = $clienteConfigEntityHandler;
        $this->mercadoLivrePerguntaEntityHandler = $mercadoLivrePerguntaEntityHandler;
        $this->mercadoLivreItemEntityHandler = $mercadoLivreItemEntityHandler;
        $this->integradorMercadoLivre = $integradorMercadoLivre;
        $this->pushMessageEntityHandler = $pushMessageEntityHandler;
        $this->syslog = $syslog->setApp('conecta')->setComponent(self::class);
    }


    /**
     * @throws ViewException
     */
    private function saveAuthInfo(ClienteConfig $clienteConfig, int $i, array $authInfo): ClienteConfig
    {
        try {
            $clienteConfig->jsonData['mercadolivre'][$i]['access_token'] = $authInfo['access_token'];
            $clienteConfig->jsonData['mercadolivre'][$i]['token_type'] = $authInfo['token_type'];
            $clienteConfig->jsonData['mercadolivre'][$i]['expires_in'] = $authInfo['expires_in'];
            $clienteConfig->jsonData['mercadolivre'][$i]['autorizado_em'] =
                (new \DateTime())->format('Y-m-d H:i:s');
            $clienteConfig->jsonData['mercadolivre'][$i]['scope'] = $authInfo['scope'];
            $clienteConfig->jsonData['mercadolivre'][$i]['refresh_token'] = $authInfo['refresh_token'];

            if (!($clienteConfig->jsonData['mercadolivre'][$i]['me'] ?? false)) {
                $me = $this->integradorMercadoLivre->getMe($authInfo['access_token']);
            } else {
                $me = $clienteConfig->jsonData['mercadolivre'][$i]['me'];
            }
            $clienteConfig->jsonData['mercadolivre'][$i]['me'] = $me;

            return $this->clienteConfigEntityHandler->save($clienteConfig);
        } catch (\Exception $e) {
            $msg = ExceptionUtils::treatException($e);
            throw new ViewException($msg, 0, $e);
        }
    }


    /**
     * @throws ViewException
     */
    public function autorizarApp(ClienteConfig $clienteConfig, int $i): void
    {
        $this->syslog->info('MercadoLivre.autorizarApp', $clienteConfig->jsonData['url_loja']);

        $r = $this->integradorMercadoLivre->autorizarApp(
            $clienteConfig->jsonData['mercadolivre'][$i]['token_tg']);

        $clienteConfig = $this->saveAuthInfo($clienteConfig, $i, $r);

        if (!($clienteConfig->jsonData['mercadolivre'][$i]['me']['id'] ?? null)) {
            $rMe = $this->integradorMercadoLivre->getMe($clienteConfig->jsonData['mercadolivre'][$i]['access_token']);
            $clienteConfig->jsonData['mercadolivre'][$i]['me'] = $rMe;
            $this->clienteConfigEntityHandler->save($clienteConfig);
        }
    }


    /**
     * @throws ViewException
     */
    public function handleAccessToken(ClienteConfig $clienteConfig, int $i): ?string
    {
        if (!($clienteConfig->jsonData['mercadolivre'][$i]['token_tg'] ?? false)) {
            $this->syslog->info('Cliente não está vinculado ao ML (sem mercadolivre.token_tg)', json_encode($clienteConfig));
            return null;
        }

        $autorizadoEm = DateTimeUtils::parseDateStr($clienteConfig->jsonData['mercadolivre'][$i]['autorizado_em']);
        $expiraEm =
            ($autorizadoEm)->add(new \DateInterval('PT' . $clienteConfig->jsonData['mercadolivre'][$i]['expires_in'] . 'S'));

        if (DateTimeUtils::diffInMinutes($expiraEm, new \DateTime()) < 60) {
            $this->syslog->info('MercadoLivre.renewAccessToken', $clienteConfig->jsonData['url_loja']);
            if (!($clienteConfig->jsonData['mercadolivre'][$i]['refresh_token'] ?? null)) {
                throw new ViewException('Impossível renovar sem mercadolivre.refresh_token');
            }
            try {
                $r = $this->integradorMercadoLivre->renewAccessToken(
                    $clienteConfig->jsonData['mercadolivre'][$i]['refresh_token']);
            } catch (\Exception $e) {
                $errMsg = 'Erro ao renewAccessToken para ' . $clienteConfig->cliente->nome . '. Talvez reautorizar?';
                $this->syslog->err($errMsg);
                throw new ViewException($errMsg);
            }
            $clienteConfig = $this->saveAuthInfo($clienteConfig, $i, $r);
        }
        if (!($clienteConfig->jsonData['mercadolivre'][$i]['me']['id'] ?? null)) {
            try {
                $rMe = $this->integradorMercadoLivre->getMe($clienteConfig->jsonData['mercadolivre'][$i]['access_token']);
                $clienteConfig->jsonData['mercadolivre'][$i]['me'] = $rMe;
                $this->clienteConfigEntityHandler->save($clienteConfig);
            } catch (ViewException $e) {
                $errMsg = 'Erro ao getMe para ' . $clienteConfig->cliente->nome . '.';
                $this->syslog->err($errMsg);
                throw new ViewException($errMsg);
            }
        }
        return $clienteConfig->jsonData['mercadolivre'][$i]['access_token'];
    }


    /**
     * @throws ViewException
     */
    public function renewAccessToken(ClienteConfig $clienteConfig, int $i): void
    {
        if (!($clienteConfig->jsonData['mercadolivre'][$i]['refresh_token'] ?? false)) {
            $this->syslog->info('refresh_token n/d', json_encode($clienteConfig));
            throw new ViewException('refresh_token n/d');
        }

        $r = $this->integradorMercadoLivre->renewAccessToken(
            $clienteConfig->jsonData['mercadolivre'][$i]['refresh_token']);
        $this->saveAuthInfo($clienteConfig, $i, $r);
    }


    public function atualizar(): JsonResponse
    {
        $this->syslog->info('MercadoLivre.getQuestionsGlobal - INI');
        $clienteConfigs = $this->mercadoLivrePerguntaEntityHandler->getDoctrine()
            ->getRepository(ClienteConfig::class)->findByAtivo(true);

        $repoMlItem = $this->mercadoLivrePerguntaEntityHandler->getDoctrine()
            ->getRepository(MercadoLivreItem::class);

        $repoMlPergunta = $this->mercadoLivrePerguntaEntityHandler->getDoctrine()
            ->getRepository(MercadoLivrePergunta::class);

        /** @var ClienteConfig $clienteConfig */
        foreach ($clienteConfigs as $clienteConfig) {
            $mls = $clienteConfig->jsonData['mercadolivre'];
            if (is_array($mls) && count($mls) > 0 && array_keys($mls)[0] === 0) {
                foreach ($mls as $i => $ml) {
                    if ($ml['access_token'] ?? false) {
                        $q = 0;
                        try {
                            $this->handleAccessToken($clienteConfig, $i);
                            $offset = $ml['questions_offset'] ?? 0;
                            $rs = $this->integradorMercadoLivre->getQuestions(
                                $ml['access_token'],
                                $offset);
                            $this->syslog->info('MercadoLivre.getQuestionsGlobal - total de perguntas: ' . count($rs), $clienteConfig->jsonData['url_loja']);
                            $offset += count($rs);
                            $ml['questions_offset'] = $offset;
                            $this->clienteConfigEntityHandler->save($clienteConfig);
                            foreach ($rs as $r) {
                                $pergunta = $repoMlPergunta->findOneByMercadolivreId($r['id']);
                                if ($pergunta) continue;
                                $item = $repoMlItem->findOneByMercadolivreId($r['item_id']);
                                if (!$item) {
                                    $item = $this->getItem($clienteConfig, $i, $r['item_id']);
                                }
                                $pergunta = new MercadoLivrePergunta();
                                $pergunta->mercadoLivreItem = $item;
                                $pergunta->mercadolivreId = $r['id'];
                                $pergunta->jsonData['r'] = $r;
                                $pergunta->status = $r['status'];
                                $pergunta->dtPergunta = DateTimeUtils::parseDateStr($r['date_created']);
                                $this->mercadoLivrePerguntaEntityHandler->save($pergunta);
                                $q++;
                            }
                        } catch (ViewException $e) {
                            $this->syslog->err('Erro na iteração do MercadoLivreBusiness::atualizar para ' .
                                $clienteConfig->cliente->nome . ' [' . $i . ']' .
                                ' (' . $e->getMessage() . ')', $e->getTraceAsString());
                        }
                        if ($q) {
                            $this->pushMessageEntityHandler
                                ->enviarMensagemParaLista(
                                    $q . " nova(s) pergunta(s) para " .
                                    $clienteConfig->cliente->nome,
                                    "MSGS_ML");
                        }
                    } else {
                        $this->syslog->info('MercadoLivre.getQuestionsGlobal - access_token n/d', $clienteConfig->jsonData['url_loja']);
                    }
                }
            } else {
                $this->syslog->info($clienteConfig->cliente->nome . ' não possui array em jsonData.mercadolivre', $clienteConfig->jsonData['url_loja']);
            }

        }

        return new JsonResponse(
            [
                'RESULT' => 'OK',
                'MSG' => 'Executado com sucesso',
            ]
        );
    }


    /**
     * @throws ViewException
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public function getClienteConfigByUserId($userId): ?ClienteConfig
    {
        try {
            $conn = $this->clienteConfigEntityHandler->getDoctrine()->getConnection();
            $r = $conn->fetchAssociative('SELECT id FROM ecomm_cliente_config WHERE json_data->>"$.mercadolivre.me.id" = :userId', ['userId' => $userId]);
            if ($r['id'] ?? null) {
                return $this->clienteConfigEntityHandler->getDoctrine()->getRepository(ClienteConfig::class)->find($r['id']);
            }
            throw new ViewException('Nenhum clienteConfig para este userId (' . $userId . ')');
        } catch (Exception $e) {
            throw new ViewException('Erro - getClienteConfigByUserId', 0, $e);
        }
    }


    /**
     * Handle chamado pelo MlNotificationHandler (que fica ouvindo as requisições enviadas pelo ML)
     *
     * @throws ViewException
     */
    public function handleMessage(string $resourceId, string $userId): void
    {
        $clienteConfig = $this->getClienteConfigByUserId($userId);
        $i = $this->getConfigsIndexByUserId($clienteConfig, $userId);
        $rs = $this->integradorMercadoLivre->getMessage(
            $clienteConfig->jsonData['mercadolivre'][$i]['access_token'],
            $resourceId
        );
    }

    /**
     * Handle chamado pelo MlNotificationHandler (que fica ouvindo as requisições enviadas pelo ML)
     *
     * @throws ViewException
     */
    public function handleQuestion(string $resourceId, string $userId): void
    {
        $clienteConfig = $this->getClienteConfigByUserId($userId);
        $i = $this->getConfigsIndexByUserId($clienteConfig, $userId);
        $this->handleAccessToken($clienteConfig, $i);
        $r = $this->integradorMercadoLivre->getQuestion(
            $clienteConfig->jsonData['mercadolivre'][$i]['access_token'],
            $resourceId
        );


        $repoMlItem = $this->mercadoLivrePerguntaEntityHandler->getDoctrine()
            ->getRepository(MercadoLivreItem::class);

        $repoMlPergunta = $this->mercadoLivrePerguntaEntityHandler->getDoctrine()
            ->getRepository(MercadoLivrePergunta::class);

        $pergunta = $repoMlPergunta->findOneByMercadolivreId($r['id']);
        if ($pergunta) return;
        $item = $repoMlItem->findOneByMercadolivreId($r['item_id']);
        if (!$item) {
            $item = $this->getItem($clienteConfig, $i, $r['item_id']);
        }
        $pergunta = new MercadoLivrePergunta();
        $pergunta->mercadoLivreItem = $item;
        $pergunta->mercadolivreId = $r['id'];
        $pergunta->jsonData['r'] = $r;
        $pergunta->status = $r['status'];
        $pergunta->dtPergunta = DateTimeUtils::parseDateStr($r['date_created']);
        $this->mercadoLivrePerguntaEntityHandler->save($pergunta);
    }


    /**
     * Handle chamado pelo MlNotificationHandler (que fica ouvindo as requisições enviadas pelo ML)
     *
     * @throws ViewException
     */
    public function handleClaim(string $resourceId, string $userId): void
    {
        $clienteConfig = $this->getClienteConfigByUserId($userId);
        $i = $this->getConfigsIndexByUserId($clienteConfig, $userId);
        $rs = $this->integradorMercadoLivre->getClaim(
            $clienteConfig->jsonData['mercadolivre'][$i]['access_token'],
            $resourceId
        );
    }


    /**
     * @throws ViewException
     */
    public function responder(MercadoLivrePergunta $pergunta, string $resposta)
    {
        $userId = $pergunta->mercadoLivreItem->mercadolivreUserId;
        $i = $this->getConfigsIndexByUserId($pergunta->mercadoLivreItem->clienteConfig, $userId);
        $this->handleAccessToken($pergunta->mercadoLivreItem->clienteConfig, $i);
        $rs = $this->integradorMercadoLivre->responder(
            $pergunta->mercadoLivreItem->clienteConfig->jsonData['mercadolivre'][$i]['access_token'],
            $pergunta->mercadolivreId,
            $resposta);
        if ($rs['status'] !== 'ANSWERED') {
            throw new ViewException('Erro ao responder pergunta');
        }
        $this->atualizarPergunta($pergunta);
    }

    /**
     * @throws ViewException
     */
    public function atualizarPergunta(MercadoLivrePergunta $pergunta)
    {
        $userId = $pergunta->mercadoLivreItem->mercadolivreUserId;
        $i = $this->getConfigsIndexByUserId($pergunta->mercadoLivreItem->clienteConfig, $userId);
        
        $this->handleAccessToken($pergunta->mercadoLivreItem->clienteConfig, $i);
        $i = $this->getConfigsIndexByUserId($pergunta->mercadoLivreItem->clienteConfig, $userId);
        $rs = $this->integradorMercadoLivre->atualizarPergunta(
            $pergunta->mercadoLivreItem->clienteConfig->jsonData['mercadolivre'][$i]['access_token'],
            $pergunta->mercadolivreId);
        $pergunta->jsonData['r'] = $rs;
        $pergunta->status = $rs['status'];
        $this->mercadoLivrePerguntaEntityHandler->save($pergunta);
    }


    /** @noinspection PhpIncompatibleReturnTypeInspection */
    /**
     * @throws ViewException
     */
    public function getItem(ClienteConfig $clienteConfig, int $i, string $id): MercadoLivreItem
    {
        $rs = $this->integradorMercadoLivre->getItem($clienteConfig->jsonData['mercadolivre'][$i]['access_token'], $id);

        if (($rs['error'] ?? '') === 'not_found') {
            $rs['title'] = 'NÃO ENCONTRADO';
            $rs['price'] = 0;
        }
        $item = new MercadoLivreItem();
        $item->clienteConfig = $clienteConfig;
        $userId = $this->getConfigsIndexByUserId($clienteConfig, $i);
        $item->mercadolivreUserId = $userId;
        $item->descricao = $rs['title'];
        $item->precoVenda = $rs['price'];
        $item->mercadolivreId = $id;
        $item->jsonData['r'] = $rs;

        return $this->mercadoLivreItemEntityHandler->save($item);
    }


    public function getConfigsIndexByUserId(ClienteConfig $clienteConfig, string $userId): int
    {
        if (!($clienteConfig->jsonData['mercadolivre'] ?? false)) {
            throw new ViewException('clienteConfig sem "mercadolivre"');
        }

        if (!is_array($clienteConfig->jsonData['mercadolivre'])) {
            throw new ViewException('clienteConfig.mercadolivre não é array');
        }

        foreach ($clienteConfig->jsonData['mercadolivre'] as $i => $mlConfigs) {
            if ((string)($mlConfigs['me']['id'] ?? '') === $userId) {
                return $i;
            }
        }
        throw new ViewException('Nenhuma configuração do mercadolivre para o userId: ' . $userId);
    }

}