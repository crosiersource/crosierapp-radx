<?php

namespace App\Security;

use CrosierSource\CrosierLibBaseBundle\Security\CrosierCoreAuthenticatorTrait;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class CrosierCoreAuthenticator.
 *
 * Autenticador padrão para o CrosierCore.
 *
 * @package App\Security
 * @author Carlos Eduardo Pauluk
 */
class CrosierCoreAuthenticator extends AbstractGuardAuthenticator
{

    use CrosierCoreAuthenticatorTrait;

}
