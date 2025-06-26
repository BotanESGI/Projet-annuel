<?php

namespace App\Controller\Checkout;

use App\Entity\Orders;
use App\Entity\OrderItem;
use App\Entity\Cart;
use App\Entity\Address;
use App\Entity\OrderAddress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class OrderController extends AbstractController
{
    #[Route('/api/cart/payment-intent', name: 'cart_payment_intent', methods: ['POST'])]
    public function paymentIntent(
        Request $request,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $shippingAddressId = $data['shippingAddressId'] ?? null;
        $billingAddressId = $data['billingAddressId'] ?? null;

        if (!$shippingAddressId) {
            return $this->json(['message' => 'Adresse de livraison requise'], 400);
        }

        $shippingAddress = $em->getRepository(Address::class)->findOneBy([
            'id' => $shippingAddressId,
            'user' => $user,
            'type' => 'shipping'
        ]);
        if (!$shippingAddress) {
            return $this->json(['message' => 'Adresse de livraison invalide'], 400);
        }

        $billingAddress = null;
        if ($billingAddressId) {
            $billingAddress = $em->getRepository(Address::class)->findOneBy([
                'id' => $billingAddressId,
                'user' => $user,
                'type' => 'billing'
            ]);
            if (!$billingAddress) {
                return $this->json(['message' => 'Adresse de facturation invalide'], 400);
            }
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart || count($cart->getItems()) === 0) {
            return $this->json(['message' => 'Panier vide'], 400);
        }

        $total = 0;
        foreach ($cart->getItems() as $cartItem) {
            $total += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
        }

        Stripe::setApiKey('sk_test_51PX6nWRv1OMvXsRITDD8oturLPyAenrdy6hArwzJWbWBdP6v0YWk7NQbeMfYsFvUB6RBpIkHF3EPWF78LVsLJJSa00SN2fDR4H');

        $paymentIntent = PaymentIntent::create([
            'amount' => intval($total * 100),
            'currency' => 'eur',
            'metadata' => [
                'user_id' => $user->getId(),
                'shipping_address_id' => $shippingAddress->getId(),
                'billing_address_id' => $billingAddress ? $billingAddress->getId() : null,
            ],
            'receipt_email' => $user->getEmail(),
        ]);

        return $this->json(['clientSecret' => $paymentIntent->client_secret]);
    }

    #[Route('/api/cart/confirm-order', name: 'cart_confirm_order', methods: ['POST'])]
    public function confirmOrder(
        Request $request,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $shippingAddressId = $data['shippingAddressId'] ?? null;
        $billingAddressId = $data['billingAddressId'] ?? null;
        $paymentIntentId = $data['paymentIntentId'] ?? null;

        if (!$shippingAddressId || !$paymentIntentId) {
            return $this->json(['message' => 'Données manquantes'], 400);
        }

        // Vérifier le paiement Stripe
        Stripe::setApiKey('sk_test_51PX6nWRv1OMvXsRITDD8oturLPyAenrdy6hArwzJWbWBdP6v0YWk7NQbeMfYsFvUB6RBpIkHF3EPWF78LVsLJJSa00SN2fDR4H');
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        if ($paymentIntent->status !== 'succeeded') {
            return $this->json(['message' => 'Paiement non validé'], 400);
        }

        $shippingAddress = $em->getRepository(Address::class)->findOneBy([
            'id' => $shippingAddressId,
            'user' => $user,
            'type' => 'shipping'
        ]);
        if (!$shippingAddress) {
            return $this->json(['message' => 'Adresse de livraison invalide'], 400);
        }

        $billingAddress = null;
        if ($billingAddressId) {
            $billingAddress = $em->getRepository(Address::class)->findOneBy([
                'id' => $billingAddressId,
                'user' => $user,
                'type' => 'billing'
            ]);
            if (!$billingAddress) {
                return $this->json(['message' => 'Adresse de facturation invalide'], 400);
            }
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart || count($cart->getItems()) === 0) {
            return $this->json(['message' => 'Panier vide'], 400);
        }

        // Création de l'entité OrderAddress à partir des adresses sélectionnées
        $orderAddress = new OrderAddress();
        $orderAddress->setShippingStreet($shippingAddress->getStreet());
        $orderAddress->setShippingCity($shippingAddress->getCity());
        $orderAddress->setShippingPostalCode($shippingAddress->getPostalCode());

        if ($billingAddress) {
            $orderAddress->setBillingStreet($billingAddress->getStreet());
            $orderAddress->setBillingCity($billingAddress->getCity());
            $orderAddress->setBillingPostalCode($billingAddress->getPostalCode());
        } else {
            $orderAddress->setBillingStreet($shippingAddress->getStreet());
            $orderAddress->setBillingCity($shippingAddress->getCity());
            $orderAddress->setBillingPostalCode($shippingAddress->getPostalCode());
        }

        $em->persist($orderAddress);

        $order = new Orders();
        $order->setUser($user);
        $order->setShippingAddress($orderAddress);
        $order->setTotal(0);
        $order->setDate(new \DateTimeImmutable());
        $total = 0;

        foreach ($cart->getItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $order->addOrderItem($orderItem);
            $em->persist($orderItem);
            $total += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
        }
        $order->setTotal($total);
        $em->persist($order);

        foreach ($cart->getItems() as $item) {
            $em->remove($item);
        }
        $em->flush();

        return $this->json(['message' => 'Commande créée avec succès']);
    }
}