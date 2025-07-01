<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TagRepository;

class HomeController extends AbstractController
{

    #[Route('/api/home', name: 'api_home', methods: ['GET'])]
    public function home(Request $request, ProductRepository $productRepository, TagRepository $tagRepository): JsonResponse
    {
        $recentIds = $request->query->all('recent');
        $tags = $tagRepository->findAll();

        return new JsonResponse([
            'bestRatedProducts' => $productRepository->findByBestRated(8),
            'cheapestProduct' => $productRepository->findByPriceASC(8),
            'recentlyViewedProducts' => $productRepository->findByIds($recentIds),
            'mostSoldProducts' => $productRepository->findMostSoldProduct(8),
            'latestProducts' => $productRepository->findLatestProducts(8),
            'tags' => array_map(fn($tag) => [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'color' => $tag->getColor(),
            ], $tags),
        ]);
    }
}