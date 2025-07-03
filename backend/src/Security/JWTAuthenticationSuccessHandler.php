<?php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTAuthenticationSuccessHandler extends AuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {

        $response = parent::onAuthenticationSuccess($request, $token);

        $data = json_decode($response->getContent(), true);

        $user = $token->getUser();
        if ($user instanceof UserInterface && method_exists($user, 'getId')) {
            $data['userId'] = $user->getId();
        }
        if ($user instanceof UserInterface && method_exists($user, 'getName')) {
            $data['userName'] = $user->getName();
        }
        if ($user instanceof UserInterface && method_exists($user, 'getLastname')) {
            $data['userLastName'] = $user->getLastname();
        }
        if ($user instanceof UserInterface && method_exists($user, 'getRoles')) {
            $data['roles'] = $user->getRoles();
        }

        return new JsonResponse($data);
    }
}