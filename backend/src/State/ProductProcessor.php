<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DigitalProduct;
use App\Entity\PhysicalProduct;
use App\Entity\Product;
use App\Entity\Category;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductProcessor implements ProcessorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Product|object $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        if ($operation->getName() === 'put') {
            $this->entityManager->persist($data);
            $this->entityManager->flush();
            return $data;
        }

        $input = $context['request_data'] ?? null;

        if (!isset($input['productType'])) {
            throw new BadRequestHttpException('Le type de produit est obligatoire (physical ou digital)');
        }

        if (!isset($input['defaultCategory']) || !is_numeric($input['defaultCategory'])) {
            throw new BadRequestHttpException('La catégorie par défaut est obligatoire');
        }

        $defaultCategory = $this->entityManager->getRepository(Category::class)->find($input['defaultCategory']);

        if (!$defaultCategory) {
            throw new BadRequestHttpException('La catégorie par défaut n\'existe pas');
        }

        if ($input['productType'] === 'physical') {
            $product = new PhysicalProduct($defaultCategory);

            if (isset($input['characteristics'])) {
                $product->setCharacteristics($input['characteristics']);
            }
        } elseif ($input['productType'] === 'digital') {
            $product = new DigitalProduct($defaultCategory);

            if (isset($input['downloadLink'])) {
                $product->setDownloadLink($input['downloadLink']);
            }

            if (isset($input['filesize'])) {
                $product->setFilesize($input['filesize']);
            }

            if (isset($input['filetype'])) {
                $product->setFiletype($input['filetype']);
            }
        } else {
            throw new BadRequestHttpException('Type de produit invalide. Utilisez physical ou digital');
        }

        if (isset($input['name'])) {
            $product->setName($input['name']);
        }

        if (isset($input['description'])) {
            $product->setDescription($input['description']);
        }

        if (isset($input['price'])) {
            $product->setPrice($input['price']);
        }

        if (isset($input['image'])) {
            $product->setImage($input['image']);
        }

        $product->setCreatedAt(new DateTime());

        if (isset($input['categories']) && is_array($input['categories'])) {
            foreach ($input['categories'] as $categoryId) {
                $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
                if ($category) {
                    $product->addCategory($category);
                }
            }
        }

        if (isset($input['tags']) && is_array($input['tags'])) {
            foreach ($input['tags'] as $tagId) {
                $tag = $this->entityManager->getRepository('App\Entity\Tag')->find($tagId);
                if ($tag) {
                    $product->addTag($tag);
                }
            }
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }
}