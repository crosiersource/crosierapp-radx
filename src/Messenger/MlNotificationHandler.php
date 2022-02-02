<?php

namespace App\Messenger;

use CrosierSource\CrosierLibRadxBundle\Messenger\Ecommerce\Message\MlNotification;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Classe criada apenas porque o symfony/messenger não aceita fazer um $bus->dispatch com uma
 * message que não tenha um Handler no projeto. Como neste caso ainda não iremos tratar a mensagem
 * aqui, então é necessário este RTA.
 * 
 * @author Carlos Eduardo Pauluk
 */
class MlNotificationHandler implements MessageHandlerInterface
{
    public function __invoke(MlNotification $message)
    {
        // não trata
        throw new RecoverableMessageHandlingException();
    }
}