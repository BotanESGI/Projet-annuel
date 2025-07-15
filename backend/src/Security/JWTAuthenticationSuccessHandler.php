<?php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JWTAuthenticationSuccessHandler extends AuthenticationSuccessHandler
{
    protected $jwtManager;

    public function setJwtManager(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): \Symfony\Component\HttpFoundation\Response
    {
        $user = $token->getUser();

        if ($user instanceof UserInterface && method_exists($user, 'getTotpSecret') && $user->getTotpSecret()) {
            $tempToken = $this->jwtManager->create($user);
            return new JsonResponse([
                '2fa_required' => true,
                'tempToken' => $tempToken,
            ]);
        }

        $response = parent::onAuthenticationSuccess($request, $token);
        $data = json_decode($response->getContent(), true);
        if (!is_array($data)) {
            $data = [];
        }

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