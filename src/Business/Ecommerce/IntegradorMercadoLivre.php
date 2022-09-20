<?php


namespace App\Business\Ecommerce;

use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use CrosierSource\CrosierLibBaseBundle\Exception\ViewException;
use CrosierSource\CrosierLibRadxBundle\Entity\Estoque\Produto;
use CrosierSource\CrosierLibRadxBundle\Entity\Vendas\Venda;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\DeptoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Estoque\ProdutoEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaEntityHandler;
use CrosierSource\CrosierLibRadxBundle\EntityHandler\Vendas\VendaItemEntityHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Regras de negócio para a integração com a Tray.
 *
 * @author Carlos Eduardo Pauluk
 */
class IntegradorMercadoLivre implements IntegradorEcommerce
{

    private Client $client;

    public string $endpoint;

    public string $accessToken;

    private Security $security;

    private ParameterBagInterface $params;

    private SyslogBusiness $syslog;

    private DeptoEntityHandler $deptoEntityHandler;

    private ProdutoEntityHandler $produtoEntityHandler;

    private VendaEntityHandler $vendaEntityHandler;

    private VendaItemEntityHandler $vendaItemEntityHandler;

    private ?array $deptosNaTray = null;

    private array $configsMercadoLivre;


    public function __construct(Security               $security,
                                ParameterBagInterface  $params,
                                SyslogBusiness         $syslog,
                                DeptoEntityHandler     $deptoEntityHandler,
                                ProdutoEntityHandler   $produtoEntityHandler,
                                VendaEntityHandler     $vendaEntityHandler,
                                VendaItemEntityHandler $vendaItemEntityHandler
    )
    {
        $this->security = $security;
        $this->params = $params;
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->deptoEntityHandler = $deptoEntityHandler;
        $this->produtoEntityHandler = $produtoEntityHandler;
        $this->vendaEntityHandler = $vendaEntityHandler;
        $this->vendaItemEntityHandler = $vendaItemEntityHandler;
        $this->client = new Client();

        $r = $this->deptoEntityHandler->getDoctrine()->getConnection()
            ->fetchAssociative('SELECT valor FROM cfg_app_config WHERE app_uuid = :appUUID AND chave = :chave',
                [
                    'appUUID' => $_SERVER['CROSIERAPP_UUID'],
                    'chave' => 'mercadolivre.configs.json'
                ]);
        if (!($r['valor'] ?? false)) {
            throw new ViewException('mercadolivre.configs.json n/d');
        }
        $this->configsMercadoLivre = json_decode($r['valor'], true);
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }


    public function autorizarApp(string $tokenTg): array
    {
        try {
            $url = $this->configsMercadoLivre['url_autoriz'];
            $response = $this->client->request('POST', $url, [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->configsMercadoLivre['client_id'],
                    'client_secret' => $this->configsMercadoLivre['client_secret'],
                    'code' => $tokenTg,
                    'redirect_uri' => $this->configsMercadoLivre['redirect_uri'],
                ]
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            return $json;
        } catch (\Throwable $e) {
            throw new ViewException('Erro ao autorizar app no Mercado Livre', 0, $e);
        }
    }


    public function renewAccessToken(string $refreshToken): array
    {
        $url = $this->configsMercadoLivre['url_autoriz'];
        $response = $this->client->request('POST', $url, [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->configsMercadoLivre['client_id'],
                'client_secret' => $this->configsMercadoLivre['client_secret'],
                'refresh_token' => $refreshToken,
            ]
        ]);

        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        return $json;
    }


    public function getQuestions(string $accessToken, int $offset): array
    {
        $url = 'https://api.mercadolibre.com/my/received_questions/search?api_version=4';
        $rs = [];
        $url = $url . '&offset=' . $offset;
        try {
            do {
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                ]);
                $bodyContents = $response->getBody()->getContents();
                $json = json_decode($bodyContents, true);
                $rs = array_merge($rs, ($json['questions'] ?? []));
                $offset += ($json['limit'] ?? 0);
                $hasResults = $json['total'] > $offset;
            } while ($hasResults);
        } catch (GuzzleException $e) {
            throw new ViewException('Erro - getQuestions (accessToken: ' . $accessToken . ') (URL: ' . $url . ')', 0, $e);
        }
        return $rs;
    }


    /**
     * https://developers.mercadolivre.com.br/en_us/products-receive-notifications#questions
     */
    public function getQuestion(string $accessToken, string $resourceId): array
    {
        $url = 'https://api.mercadolibre.com' . $resourceId;
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            return $json;
        } catch (GuzzleException $e) {
            throw new ViewException('Erro - getQuestion (accessToken: ' . $accessToken . ') (resourceId: ' . $resourceId . ')', 0, $e);
        }
    }

    /**
     * https://developers.mercadolivre.com.br/en_us/products-receive-notifications#messages
     */
    public function getMessage(string $accessToken, string $resourceId): array
    {
        $url = 'https://api.mercadolibre.com/messages/' . $resourceId;
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            return $json;
        } catch (GuzzleException $e) {
            throw new ViewException('Erro - getMessage (accessToken: ' . $accessToken . ') (resourceId: ' . $resourceId . ')', 0, $e);
        }
    }

    /**
     * https://developers.mercadolivre.com.br/en_us/products-receive-notifications#claims
     */
    public function getClaim(string $accessToken, string $resourceId): array
    {
        $url = 'https://api.mercadolibre.com/' . $resourceId;
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            return $json;
        } catch (GuzzleException $e) {
            throw new ViewException('Erro - getMessage (accessToken: ' . $accessToken . ') (resourceId: ' . $resourceId . ')', 0, $e);
        }
    }


    public function responder(string $accessToken, string $questionId, string $text): array
    {
        try {
            $url = 'https://api.mercadolibre.com/answers?api_version=4';
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::JSON => [
                    'question_id' => $questionId,
                    'text' => $text,
                ]
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            return $json;
        } catch (\Throwable $e) {
            throw new ViewException('Erro ao responder pergunta no Mercado Livre', 0, $e);
        }
    }


    public function atualizarPergunta(string $accessToken, string $questionId): array
    {
        try {
            $url = 'https://api.mercadolibre.com/questions/' . $questionId . '?api_version=4';
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
            $bodyContents = $response->getBody()->getContents();
            $json = json_decode($bodyContents, true);
            return $json;
        } catch (GuzzleException $e) {
            throw new ViewException('Erro ao atualizar a pergunta', 0, $e);
        }
    }


    public function getItem(string $accessToken, string $itemId): array
    {
        $url = 'https://api.mercadolibre.com/items?ids=' . $itemId . '&api_version=4';
        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        return $json[0]['body'] ?? [];
    }


    public function getMe(string $accessToken): array
    {
        $url = 'https://api.mercadolibre.com/users/me';
        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $bodyContents = $response->getBody()->getContents();
        $json = json_decode($bodyContents, true);
        return $json ?? [];
    }

    public function obterVendas(\DateTime $dtVenda, ?bool $resalvar = false): int
    {
        // TODO: Implement obterVendas() method.
        return 0;
    }

    public function obterVendasPorData(\DateTime $dtVenda)
    {
        // TODO: Implement obterVendasPorData() method.
    }

    public function obterCliente($idClienteEcommerce)
    {
        // TODO: Implement obterCliente() method.
    }

    public function reintegrarVendaParaCrosier(Venda $venda)
    {
        // TODO: Implement reintegrarVendaParaCrosier() method.
    }

    public function integrarVendaParaEcommerce(Venda $venda)
    {
        // TODO: Implement integrarVendaParaEcommerce() method.
    }

    public function integraProduto(Produto $produto, ?bool $integrarImagens = true, ?bool $respeitarDelay = false)
    {
        // TODO: Implement integraProduto() method.
    }


}
