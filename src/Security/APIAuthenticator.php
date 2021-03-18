<?php

namespace App\Security;

use CrosierSource\CrosierLibBaseBundle\Security\APIAuthenticatorTrait;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Autenticação por API Token.
 *
 * @author Carlos Eduardo Pauluk
 */
class APIAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{

    use APIAuthenticatorTrait;
}
