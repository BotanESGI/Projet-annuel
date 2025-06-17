<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsController]
class AuthentificationController
{
    #[Route('/api/register', name: 'register_post', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'], $data['password'], $data['nom'], $data['lastname'])) {
                return new JsonResponse([
                    'error' => 'Données manquantes',
                    'required_fields' => ['email', 'password', 'nom', 'lastname']
                ], Response::HTTP_BAD_REQUEST);
            }

            // Vérification si l'email existe déjà
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse([
                    'error' => 'Un compte existe déjà avec cet email'
                ], Response::HTTP_CONFLICT);
            }

            $user = new User();
            $user->setEmail($data['email'])
                ->setName($data['nom'])
                ->setLastname($data['lastname'])
                ->setPassword($passwordHasher->hashPassword($user, $data['password']))
                ->setIsVerified(false)
                ->setConfirmationToken(bin2hex(random_bytes(32)));

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $em->persist($user);
            $em->flush();

            // Génération du token JWT
            $token = $jwtManager->create($user);

            return new JsonResponse([
                'message' => 'Inscription réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nom' => $user->getName(),
                    'lastname' => $user->getLastname(),
                    'isVerified' => $user->getIsVerified()
                ]
            ], Response::HTTP_CREATED);

        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse([
                'error' => 'Cette adresse email est déjà utilisée'
            ], Response::HTTP_CONFLICT);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de l\'inscription',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/me', name: 'me_api_get', methods: ['GET'])]
    public function me(#[CurrentUser] ?UserInterface $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail(),
                'nom' => $user->getName(),
                'lastname' => $user->getLastname(),
                'isVerified' => $user->getIsVerified(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

}
