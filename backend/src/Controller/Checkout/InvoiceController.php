<?php

namespace App\Controller\Checkout;

use App\Entity\Invoice;
use App\Entity\Orders;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class InvoiceController extends AbstractController
{
    public function generateInvoicePdf(Invoice $invoice, array $orderItems): Response
    {
        $order = $invoice->getOrder();
        $pdfDir = $this->getParameter('kernel.project_dir') . '/public/invoices/';

        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }

        $pdfFilename = 'invoice_' . uniqid() . '.pdf';
        $pdfPath = $pdfDir . $pdfFilename;

        $dompdf = new Dompdf();
        $html = $this->renderView('invoice/invoice.html.twig', [
            'order' => $order,
            'invoice' => $invoice,
            'orderItems' => $orderItems
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($pdfPath, $dompdf->output());

        return $this->json(['path' => '/invoices/' . $pdfFilename]);
    }

    #[Route('/api/invoices/{id}/download', name: 'invoice_download', methods: ['GET'])]
    public function downloadInvoice(
        int $id,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $invoice = $em->getRepository(Invoice::class)->find($id);
        if (!$invoice || $invoice->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Facture introuvable'], 404);
        }

        $pdfPath = $invoice->getPdfPath();
        if (!$pdfPath || !file_exists($this->getParameter('kernel.project_dir') . '/public' . $pdfPath)) {
            return $this->json(['message' => 'Fichier de facture non disponible'], 404);
        }

        $response = new BinaryFileResponse($this->getParameter('kernel.project_dir') . '/public' . $pdfPath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            'attachment',
            'facture_' . $invoice->getId() . '.pdf'
        );

        return $response;
    }
}