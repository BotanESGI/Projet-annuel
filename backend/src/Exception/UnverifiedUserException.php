<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UnverifiedUserException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Votre compte n\'est pas encore vérifié.';
    }
}
