<?php

namespace App\Controller\Checkout;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    #[Route('/api/cart/add', name: 'cart_add', methods: ['POST'])]
    public function addToCart(
        Request $request,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;
        $quantity = max(1, (int)($data['quantity'] ?? 1));

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            return $this->json(['message' => 'Produit introuvable'], 404);
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $em->persist($cart);
        }

        $cartItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct() && $item->getProduct()->getId() === $product->getId()) {
                $cartItem = $item;
                break;
            }
        }
        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cartItem->setCart($cart);
            $em->persist($cartItem);
            $cart->addItem($cartItem);
        }

        $em->flush();

        return $this->json(['message' => 'Produit ajouté au panier']);
    }

    #[Route('/api/cart', name: 'cart_get', methods: ['GET'])]
    public function getCart(
        EntityManagerInterface $em,
        Security $security,
        SerializerInterface $serializer
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart) {
            return $this->json(['items' => []]);
        }

        $items = [];
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $items[] = [
                'id' => $item->getId(),
                'quantity' => $item->getQuantity(),
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'image' => $product->getImage(),
                ]
            ];
        }

        return $this->json(['items' => $items]);
    }

    #[Route('/api/cart/item/{id}', name: 'cart_update_item', methods: ['PUT'])]
    public function updateCartItem(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $cartItem = $em->getRepository(CartItem::class)->find($id);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = max(1, (int)($data['quantity'] ?? 1));
        $cartItem->setQuantity($quantity);
        $em->flush();

        return $this->json(['message' => 'Quantité mise à jour']);
    }

    #[Route('/api/cart/item/{id}', name: 'cart_remove_item', methods: ['DELETE'])]
    public function removeCartItem(
        int $id,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $cartItem = $em->getRepository(CartItem::class)->find($id);
        if (!$cartItem || $cartItem->getCart()->getUser() !== $user) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }

        $em->remove($cartItem);
        $em->flush();

        return $this->json(['message' => 'Article supprimé']);
    }

    #[Route('/api/cart', name: 'cart_clear', methods: ['DELETE'])]
    public function clearCart(
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart) {
            return $this->json(['message' => 'Panier déjà vide']);
        }

        foreach ($cart->getItems() as $item) {
            $em->remove($item);
        }
        $em->flush();

        return $this->json(['message' => 'Panier vidé']);
    }
}