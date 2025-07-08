<?php

namespace App\Controller\Admin;

use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Category;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
}