<?php


namespace App\Messenger\Ecommerce\Message;


/**
 * Class IntegrarProdutoEcommerceMessage
 * @package App\Messenger\Message
 */
class IntegrarProdutoEcommerceMessage
{

    public int $produtoId;
    
    public bool $integrarImagens = false;

    public function __construct(int $produtoId, ?bool $integrarImagens = false)
    {
        $this->produtoId = $produtoId;
        $this->integrarImagens = $integrarImagens;
    }


}