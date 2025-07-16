<?php

namespace App\Controller\Review;

use App\Entity\OrderItem;
use App\Entity\Orders;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Entity\Product;
use App\Entity\Review;
use App\Enum\ReviewStatusEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ReviewController extends AbstractController
{
    #[Route('/api/reviews_create', name: 'create_review', methods: ['POST'])]
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'], $data['rating'], $data['product'])) {
            return $this->json(['message' => 'Champs requis manquants (content, rating, product)'], Response::HTTP_BAD_REQUEST);
        }

        $productIri = $data['product'];
        $productId = (int) preg_replace('/[^0-9]/', '', $productIri);
        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            return $this->json(['message' => 'Produit introuvable'], Response::HTTP_NOT_FOUND);
        }

        $review = new Review();
        $review->setContent($data['content']);
        $review->setRating((int)$data['rating']);
        $review->setProduct($product);
        $review->setUser($user);
        $review->setDatePublication(new \DateTimeImmutable());
        $review->setStatus(ReviewStatusEnum::PENDING);

        $errors = $validator->validate($review);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $violation) {
                $errorsArray[] = [
                    'property' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return $this->json(['violations' => $errorsArray], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($review);
        $entityManager->flush();

        return $this->json(
            $serializer->normalize($review, 'json', ['groups' => ['review:read']]),
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/reviews_edit/{id}', name: 'edit_review', methods: ['PUT'])]
    public function editReview(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $review = $entityManager->getRepository(Review::class)->find($id);
        if (!$review) {
            return $this->json(['message' => 'Avis introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est bien l'auteur
        if ($review->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException('Vous ne pouvez modifier que votre propre avis.');
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $review->setContent($data['content']);
        }
        if (isset($data['rating'])) {
            $review->setRating((int)$data['rating']);
        }

        $review->setStatus(ReviewStatusEnum::PENDING);

        $errors = $validator->validate($review);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $violation) {
                $errorsArray[] = [
                    'property' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return $this->json(['violations' => $errorsArray], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json(
            $serializer->normalize($review, 'json', ['groups' => ['review:read']]),
            Response::HTTP_OK
        );
    }

    #[Route('/api/check-purchase/{productId}', methods: ['GET'])]
    public function checkPurchase(int $productId, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['hasPurchased' => false]);
        }

        $orderItem = $entityManager->getRepository(OrderItem::class)
            ->createQueryBuilder('oi')
            ->join('oi.order', 'o')
            ->join('oi.product', 'p')
            ->where('p.id = :productId')
            ->andWhere('o.user = :userId')
            ->setParameter('productId', $productId)
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult();

        return new JsonResponse([
            'hasPurchased' => $orderItem !== null
        ]);
    }
}