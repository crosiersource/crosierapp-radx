<?php

namespace App\Security;

use CrosierSource\CrosierLibBaseBundle\Security\CrosierCoreAuthenticatorTrait;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * @author Carlos Eduardo Pauluk
 */
class CrosierCoreAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{

    use CrosierCoreAuthenticatorTrait;
}
