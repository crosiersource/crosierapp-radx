<?php


namespace App\Messenger\Ecommerce\Message;


use CrosierSource\CrosierLibBaseBundle\Business\Config\SyslogBusiness;
use App\Business\Ecommerce\MercadoLivreBusiness;
use App\Messenger\Ecommerce\Message\MlNotification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class MlNotificationHandler implements MessageHandlerInterface
{

    private SyslogBusiness $syslog;

    private MercadoLivreBusiness $mlBusiness;


    public function __construct(SyslogBusiness       $syslog,
                                MercadoLivreBusiness $mlBusiness)
    {
        $this->syslog = $syslog->setApp('radx')->setComponent(self::class);
        $this->mlBusiness = $mlBusiness;
    }

    /**
     * @throws \CrosierSource\CrosierLibBaseBundle\Exception\ViewException
     */
    public function __invoke(MlNotification $message)
    {
        $this->syslog->info('queue: consumindo MlNotification');
        $content = json_decode($message->getContent(), true);
        $topic = $content['topic'];
        switch ($topic) {
            case "messages":
                $this->mlBusiness->handleMessage($content['resource'], $content['user_id']);
                break;
            case "questions":
                $this->mlBusiness->handleQuestion($content['resource'], $content['user_id']);
                break;
            case "claims":
                $this->mlBusiness->handleClaim($content['resource'], $content['user_id']);
                break;
            default:
                $this->syslog->err('Ainda não sei tratar o ' . $topic, json_encode($content));
        }
    }
}