<?php

namespace App\Controller\Security;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class AuthController extends AbstractController
{
    private string $appUrl;
    private string $mailerFrom;

    public function __construct(string $appUrl, string $mailerFrom)
    {
        $this->appUrl = $appUrl;
        $this->mailerFrom = $mailerFrom;
    }


    #[Route('/api/register', name: 'register_post', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager,
        MailerInterface $mailer
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

            // Envoi de l'email de vérification
            $verificationUrl = $this->appUrl . '/account-verification/' . $user->getConfirmationToken();

            $email = (new Email())
                ->from($this->mailerFrom)
                ->to($user->getEmail())
                ->subject('Vérification de votre compte')
                ->html($this->renderView('emails/security/account_verification.html.twig', [
                    'user' => $user,
                    'verificationUrl' => $verificationUrl
                ]));

            $mailer->send($email);

            // Génération du token JWT
            $token = $jwtManager->create($user);

            return new JsonResponse([
                'message' => 'Inscription réussie ! Veuillez vérifier votre boîte mail pour activer votre compte.',
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

    #[Route('/api/verify-email/{token}', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(
        string $token,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $em->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            return new JsonResponse([
                'valid' => false,
                'message' => 'Token de vérification invalide ou expiré'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);
        $em->flush();

        return new JsonResponse([
            'valid' => true,
            'message' => 'Votre compte a été vérifié avec succès'
        ]);
    }

    #[Route('/api/reset-password', name: 'reset_password_request', methods: ['POST'])]
    public function requestPasswordReset(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'])) {
                return new JsonResponse([
                    'error' => 'Adresse email manquante'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

            if (!$user) {
                return new JsonResponse([
                    'message' => 'Si un compte existe avec cette adresse, un email de réinitialisation a été envoyé'
                ]);
            }

            $resetToken = bin2hex(random_bytes(32));
            $user->setResetToken($resetToken);

            $em->flush();

            $resetUrl = $this->appUrl . '/reset-password/' . $resetToken;

            $email = (new Email())
                ->from('noreply@mini-ecommerce.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->html($this->renderView('emails/security/reset_password.html.twig', [
                    'user' => $user,
                    'resetUrl' => $resetUrl
                ]));

            $mailer->send($email);

            return new JsonResponse([
                'message' => 'Si un compte existe avec cette adresse, un email de réinitialisation a été envoyé'
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la demande de réinitialisation',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/reset-password/verify/{token}', name: 'reset_password_verify', methods: ['GET'])]
    public function verifyResetToken(
        string $token,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            return new JsonResponse([
                'valid' => false,
                'message' => 'Token invalide ou expiré'
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'valid' => true
        ]);
    }

    #[Route('/api/reset-password/confirm', name: 'reset_password_confirm', methods: ['POST'])]
    public function confirmPasswordReset(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['token'], $data['password'])) {
                return new JsonResponse([
                    'error' => 'Données manquantes'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $data['token']]);

            if (!$user) {
                return new JsonResponse([
                    'error' => 'Token invalide ou expiré'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            $user->setResetToken(null);

            $em->flush();

            return new JsonResponse([
                'message' => 'Mot de passe réinitialisé avec succès'
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la réinitialisation du mot de passe',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}