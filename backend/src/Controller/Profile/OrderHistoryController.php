<?php

namespace App\Controller\Profile;

use App\Entity\Orders;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Attribute\AsController;

#[AsController]
class OrderHistoryController extends AbstractController
{
    #[Route('/api/my-orders', name: 'user_orders', methods: ['GET'])]
    public function my_orders(EntityManagerInterface $em, Security $security): JsonResponse
    {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié'], 401);
        }

        $orders = $em->getRepository(Orders::class)->findBy(['user' => $user], ['date' => 'DESC']);
        $result = [];

        foreach ($orders as $order) {
            $itemsCount = 0;
            foreach ($order->getOrderItems() as $item) {
                $itemsCount += $item->getQuantity();
            }
            $result[] = [
                'id' => $order->getId(),
                'date' => $order->getDate()?->format('Y-m-d H:i:s'),
                'total' => $order->getTotal(),
                'itemsCount' => $itemsCount,
            ];
        }

        return new JsonResponse(['orders' => $result]);
    }

    #[Route('/api/orders_details/{id}', name: 'order_detail', methods: ['GET'])]
    public function my_orders_detail(
        int $id,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié'], 401);
        }

        $order = $em->getRepository(\App\Entity\Orders::class)->find($id);
        if (!$order || $order->getUser() !== $user) {
            return new JsonResponse(['message' => 'Commande non trouvée'], 404);
        }

        $items = [];
        foreach ($order->getOrderItems() as $item) {
            $product = $item->getProduct();
            $productData = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'image' => $product->getImage(),
                'defaultCategory' => [
                    'id' => $product->getDefaultCategory()?->getId(),
                    'name' => $product->getDefaultCategory()?->getName(),
                ],
                '@type' => (new \ReflectionClass($product))->getShortName(),
            ];

            if ($product instanceof \App\Entity\DigitalProduct) {
                $productData['downloadLink'] = $product->getDownloadLink();
                $productData['filesize'] = $product->getFilesize();
                $productData['filetype'] = $product->getFiletype();
            }

            $items[] = [
                'id' => $item->getId(),
                'quantity' => $item->getQuantity(),
                'product' => $productData,
            ];
        }

        $shipping = $order->getShippingAddress();
        $shippingAddress = [
            'street' => $shipping->getShippingStreet(),
            'city' => $shipping->getShippingCity(),
            'postalCode' => $shipping->getShippingPostalCode(),
        ];
        $billingAddress = [
            'street' => $shipping->getBillingStreet(),
            'city' => $shipping->getBillingCity(),
            'postalCode' => $shipping->getBillingPostalCode(),
        ];

        return new JsonResponse([
            'order' => [
                'id' => $order->getId(),
                'date' => $order->getDate()?->format('Y-m-d H:i:s'),
                'total' => $order->getTotal(),
                'shippingAddress' => $shippingAddress,
                'billingAddress' => $billingAddress,
                'items' => $items,
            ]
        ]);
    }
}