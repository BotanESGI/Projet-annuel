<?php

namespace App\Controller\Profile;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class AccountController
{
    #[Route('/api/account', name: 'account_get', methods: ['GET'])]
    public function getAccount(#[CurrentUser] ?UserInterface $user): JsonResponse
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
                'roles' => $user->getRoles(),
                'totpSecret' => $user->getTotpSecret()
            ]
        ]);
    }

    #[Route('/api/account', name: 'account_update', methods: ['PUT'])]
    public function updateAccount(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        #[CurrentUser] ?UserInterface $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['email'])) {
                unset($data['email']);
            }

            if (isset($data['nom'])) {
                $user->setName($data['nom']);
            }

            if (isset($data['lastname'])) {
                $user->setLastname($data['lastname']);
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            }

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $em->flush();

            return new JsonResponse([
                'user' => [
                    'email' => $user->getEmail(),
                    'nom' => $user->getName(),
                    'lastname' => $user->getLastname(),
                    'isVerified' => $user->getIsVerified(),
                    'roles' => $user->getRoles()
                ]
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'errors' => ['general' => 'Une erreur est survenue lors de la mise à jour du compte', 'details' => $e->getMessage()]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/account/delete/{type}', name: 'account_delete', methods: ['DELETE'])]
    public function deleteAccount(
        string $type,
        EntityManagerInterface $em,
        #[CurrentUser] ?UserInterface $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array($type, ['hard', 'soft'])) {
            return new JsonResponse(['error' => 'Type de suppression invalide'], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($type === 'soft') {
                // soft delete
                $user->setIsDeleted(true);
                $message = 'Votre compte a été désactivé avec succès';
            } else {
                // hard delete
                $userId = $user->getId();
                $conn = $em->getConnection();
                $conn->beginTransaction();

                try {
                    $conn->executeStatement('DELETE FROM cart WHERE user_id = :id', ['id' => $userId]);
                    $conn->executeStatement('DELETE FROM orders WHERE user_id = :id', ['id' => $userId]);
                    $conn->executeStatement('DELETE FROM review WHERE user_id = :id', ['id' => $userId]);
                    $conn->executeStatement('DELETE FROM invoice WHERE user_id = :id', ['id' => $userId]);
                    $conn->executeStatement('DELETE FROM address WHERE user_id = :id', ['id' => $userId]);
                    $conn->executeStatement('DELETE FROM user WHERE id = :id', ['id' => $userId]);

                    $conn->commit();
                    $message = 'Votre compte a été définitivement supprimé';
                } catch (\Exception $e) {
                    $conn->rollBack();
                    throw $e;
                }
            }

            $em->flush();

            return new JsonResponse([
                'message' => $message
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'errors' => [
                    'general' => 'Une erreur est survenue lors de la suppression du compte',
                    'details' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
