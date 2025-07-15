<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Invoice;
use App\Entity\Orders;

class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private string $appUrl;
    private string $mailerFrom;

    public function __construct(EntityManagerInterface $entityManager, string $appUrl, string $mailerFrom)
    {
        $this->entityManager = $entityManager;
        $this->appUrl = $appUrl;
        $this->mailerFrom = $mailerFrom;
    }

    #[Route('/api/orders', name: 'admin_orders', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function orders(OrdersRepository $ordersRepository): JsonResponse
    {
        $orders = $ordersRepository->findAll();

        $data = array_map(function($order) {
            return [
                'id' => $order->getId(),
                'date' => $order->getDate()?->format('Y-m-d H:i:s'),
                'total' => $order->getTotal(),
            ];
        }, $orders);

        return $this->json($data);
    }

    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'Aucun fichier envoyé'], 400);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json(['error' => 'Format de fichier non autorisé.'], 400);
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/images';
        $filename = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($uploadsDir, $filename);
        } catch (FileException $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'upload',
                'exception' => $e->getMessage()
            ], 500);
        }

        return $this->json(['url' => '/images/' . $filename]);
    }

    #[Route('/api/admin/categories/{id}/delete-with-products', name: 'admin_category_delete_with_products', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteCategoryWithProducts(
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $em
    ): Response {
        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            return $this->json(['error' => 'Catégorie non trouvée'], 404);
        }

        $products = $productRepository->findBy(['defaultCategory' => $category]);
        foreach ($products as $product) {
            $em->remove($product);
        }

        $em->remove($category);
        $em->flush();

        return $this->json(['message' => 'Catégorie et produits associés supprimés.']);
    }

    #[Route('/api/admin/users', name: 'admin_user_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setName($data['name'] ?? '');
        $user->setLastname($data['lastname'] ?? '');
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        $user->setIsVerified($data['isVerified'] ?? false);
        $user->setIsDeleted($data['isDeleted'] ?? false);
        $user->setResetToken($data['resetToken'] ?? null);
        $user->setConfirmationToken($data['confirmationToken'] ?? null);

        if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
            return $this->json(['error' => 'Cet email est déjà utilisé.'], 400);
        }

        $plainPassword = $data['password'] ?? '';
        $violations = $validator->validatePropertyValue(User::class, 'password', $plainPassword);
        if (count($violations) > 0) {
            return $this->json(['error' => $violations[0]->getMessage()], 400);
        }
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['error' => $errors[0]->getMessage()], 400);
        }

        $em->persist($user);
        $em->flush();

        if (!$user->getIsVerified()) {
            $user->setConfirmationToken(bin2hex(random_bytes(32)));
            $em->flush();

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
        }

        return $this->json(['message' => 'Utilisateur créé', 'id' => $user->getId()]);
    }

    #[Route('/api/admin/users/{id}', name: 'admin_user_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): JsonResponse {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            if ($userRepository->findOneBy(['email' => $data['email']])) {
                return $this->json(['error' => 'Cet email est déjà utilisé.'], 400);
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['name'])) $user->setName($data['name']);
        if (isset($data['lastname'])) $user->setLastname($data['lastname']);
        if (isset($data['roles'])) $user->setRoles($data['roles']);
        if (isset($data['isVerified'])) $user->setIsVerified($data['isVerified']);
        if (isset($data['isDeleted'])) $user->setIsDeleted($data['isDeleted']);
        if (array_key_exists('resetToken', $data)) $user->setResetToken($data['resetToken']);
        if (array_key_exists('confirmationToken', $data)) $user->setConfirmationToken($data['confirmationToken']);

        if (!empty($data['password'])) {
            $violations = $validator->validatePropertyValue(User::class, 'password', $data['password']);
            if (count($violations) > 0) {
                return $this->json(['error' => $violations[0]->getMessage()], 400);
            }
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['error' => $errors[0]->getMessage()], 400);
        }

        $em->flush();

        return $this->json(['message' => 'Utilisateur modifié']);
    }

    #[Route('api/admin/invoices', name: 'admin_invoice_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $invoices = $em->getRepository(Invoice::class)->findAll();
        $data = array_map(function (Invoice $invoice) {
            return [
                'id' => $invoice->getId(),
                'totalAmount' => $invoice->getTotalAmount(),
                'user' => [
                    'id' => $invoice->getUser()?->getId(),
                    'name' => $invoice->getUser()?->getName(),
                    'lastname' => $invoice->getUser()?->getLastname(),
                    'email' => $invoice->getUser()?->getEmail(),
                ],
                'order' => $invoice->getOrder() ? [
                    'id' => $invoice->getOrder()->getId(),
                    'total' => $invoice->getOrder()->getTotal(),
                ] : null,
                'pdfPath' => $invoice->getPdfPath(),
                'paymentId' => $invoice->getPaymentId(),
            ];
        }, $invoices);

        return $this->json($data);
    }

    #[Route('api/admin/invoices_get/{id}', name: 'admin_invoice_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $invoice = $em->getRepository(Invoice::class)->find($id);
        if (!$invoice) {
            return $this->json(['error' => 'Facture non trouvée'], 404);
        }
        return $this->json([
            'id' => $invoice->getId(),
            'totalAmount' => $invoice->getTotalAmount(),
            'user' => [
                'id' => $invoice->getUser()?->getId(),
                'name' => $invoice->getUser()?->getName(),
                'lastname' => $invoice->getUser()?->getLastname(),
                'email' => $invoice->getUser()?->getEmail(),
            ],
            'order' => $invoice->getOrder() ? [
                'id' => $invoice->getOrder()->getId(),
                'total' => $invoice->getOrder()->getTotal(),
            ] : null,
            'pdfPath' => $invoice->getPdfPath(),
            'paymentId' => $invoice->getPaymentId(),
        ]);
    }

    #[Route('api/admin/invoices_create', name: 'admin_invoice_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $invoice = new Invoice();
        $invoice->setTotalAmount($data['totalAmount'] ?? 0);

        $user = $em->getRepository(User::class)->find($data['user'] ?? null);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 400);
        }
        $invoice->setUser($user);

        $order = $em->getRepository(Orders::class)->find($data['order'] ?? null);
        if ($order) {
            $invoice->setOrder($order);
        }

        $invoice->setPdfPath($data['pdfPath'] ?? null);
        $invoice->setPaymentId($data['paymentId'] ?? null);

        $errors = $validator->validate($invoice);
        if (count($errors) > 0) {
            return $this->json(['violations' => array_map(fn($e) => $e->getMessage(), iterator_to_array($errors))], 400);
        }

        $em->persist($invoice);
        $em->flush();

        return $this->json(['message' => 'Facture créée', 'id' => $invoice->getId()]);
    }

    #[Route('api/admin/invoices_update/{id}', name: 'admin_invoice_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(int $id, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $invoice = $em->getRepository(Invoice::class)->find($id);
        if (!$invoice) {
            return $this->json(['error' => 'Facture non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['totalAmount'])) $invoice->setTotalAmount($data['totalAmount']);
        if (isset($data['user'])) {
            $user = $em->getRepository(User::class)->find($data['user']);
            if (!$user) return $this->json(['error' => 'Utilisateur non trouvé'], 400);
            $invoice->setUser($user);
        }
        if (!empty($data['order'])) {
            $order = $em->getRepository(Orders::class)->find($data['order']);
            if ($order) {
                $invoice->setOrder($order);
            }
        }
        if (array_key_exists('pdfPath', $data)) $invoice->setPdfPath($data['pdfPath']);
        if (array_key_exists('paymentId', $data)) $invoice->setPaymentId($data['paymentId']);

        $errors = $validator->validate($invoice);
        if (count($errors) > 0) {
            return $this->json(['violations' => array_map(fn($e) => $e->getMessage(), iterator_to_array($errors))], 400);
        }

        $em->flush();

        return $this->json(['message' => 'Facture modifiée']);
    }

    #[Route('api/admin/invoices_delete/{id}', name: 'admin_invoice_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteInvoice(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $invoice = $this->entityManager->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->json(['error' => 'Facture non trouvée'], 404);
        }

        $invoiceId = $invoice->getId();
        $order = $invoice->getOrder();

        if ($order) {
            $orderId = $order->getId();
            $entityManager->getConnection()->executeStatement(
                'UPDATE `invoice` SET `order_id` = NULL WHERE `id` = :id',
                ['id' => $invoiceId]
            );
            $entityManager->getConnection()->executeStatement(
                'UPDATE `orders` SET `invoice_id` = NULL WHERE `id` = :id',
                ['id' => $orderId]
            );
            $entityManager->getConnection()->executeStatement(
                'DELETE FROM `invoice` WHERE `id` = :id',
                ['id' => $invoiceId]
            );
        } else {
            $entityManager->getConnection()->executeStatement(
                'DELETE FROM `invoice` WHERE `id` = :id',
                ['id' => $invoiceId]
            );
        }

        return $this->json(['message' => 'Facture supprimée']);
    }

    #[Route('/api/invoices/{id}/download', name: 'invoice_download', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function downloadInvoice(int $id, EntityManagerInterface $em): Response
    {
        $invoice = $em->getRepository(Invoice::class)->find($id);
        if (!$invoice || !$invoice->getPdfPath()) {
            return $this->json(['error' => 'Facture ou PDF introuvable'], 404);
        }

        $pdfPath = $invoice->getPdfPath();
        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $pdfPath;
        if (!file_exists($filePath)) {
            return $this->json(['error' => 'Fichier PDF non trouvé'], 404);
        }

        return $this->file($filePath, 'facture-' . $invoice->getId() . '.pdf');
    }
}