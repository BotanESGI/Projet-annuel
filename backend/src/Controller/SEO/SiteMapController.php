<?php
namespace App\Controller\SEO;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteMapController extends AbstractController
{
    private string $appUrl;

    public function __construct(string $appUrl)
    {
        $this->appUrl = $appUrl;
    }

    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(): Response
    {
        $baseUrl = rtrim($this->getParameter('app.url'), '/');

        $staticRoutes = [
            '/',
            '/products',
            '/contact',
            '/account',
            '/profile',
            '/addresses',
            '/orders',
            '/login',
            '/register',
            '/forget-password',
            '/cart',
            '/checkout',
        ];

        $dynamicRoutes = [
            '/product/x',
            '/order/x/confirmation',
            '/orders/x',
            '/reset-password/x',
            '/account-verification/x',
        ];

        $urls = [];

        foreach (array_merge($staticRoutes, $dynamicRoutes) as $path) {
            $urls[] = ['loc' => $baseUrl . $path];
        }

        $response = $this->render('sitemap/index.xml.twig', [
            'urls' => $urls,
        ]);

        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}