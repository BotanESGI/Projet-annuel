<?php

namespace App\Controller\Admin;

use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
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
}