<?php

namespace App\Controller\Admin;

use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}