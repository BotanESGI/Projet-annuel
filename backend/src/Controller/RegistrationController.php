<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class RegistrationController
{
    #[Route('/test', name: 'api_test', methods: ['GET'])]
    public function test(): Response
    {
        return new Response('Test route OK');
    }

    #[Route('/register', name: 'register_post', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            $user = new User();
            $user->setEmail($data['email'] ?? '');
            $user->setName($data['nom'] ?? '');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $passwordHasher->hashPassword($user, $data['password'])
            );

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return new JsonResponse(['errors' => (string)$errors], 400);
            }

            $em->persist($user);
            $em->flush();

            $token = $jwtManager->create($user);

            return new JsonResponse(['token' => $token], 201);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }
    }
}
