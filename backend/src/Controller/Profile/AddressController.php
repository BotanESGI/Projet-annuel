<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class AddressController
{
    #[Route('/api/addresses', name: 'addresses_get', methods: ['GET'])]
    public function getAddresses(#[CurrentUser] ?User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $addresses = $em->getRepository(Address::class)->findBy(['user' => $user]);
        $formattedAddresses = [];

        foreach ($addresses as $address) {
            $formattedAddresses[] = [
                'id' => $address->getId(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'postalCode' => $address->getPostalCode(),
                'isDefault' => $address->isDefault(),
                'isDefaultBilling' => $address->isDefaultBilling(),
                'type' => $address->getType()
            ];
        }

        return new JsonResponse(['addresses' => $formattedAddresses]);
    }

    #[Route('/api/addresses', name: 'addresses_post', methods: ['POST'])]
    public function createAddress(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['street'], $data['city'], $data['postalCode'])) {
                return new JsonResponse([
                    'error' => 'Données manquantes',
                    'required_fields' => ['street', 'city', 'postalCode']
                ], Response::HTTP_BAD_REQUEST);
            }

            $address = new Address();
            $address->setStreet($data['street'])
                ->setCity($data['city'])
                ->setPostalCode($data['postalCode'])
                ->setUser($user);

            if (isset($data['type'])) {
                $address->setType($data['type']);
            }

            if (isset($data['type']) && $data['type'] === 'shipping') {
                if (isset($data['isDefault']) && $data['isDefault']) {
                    $address->setIsDefault(true);
                    $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'isDefault' => true]);
                    foreach ($userAddresses as $existingAddress) {
                        $existingAddress->setIsDefault(false);
                    }
                } else {
                    $address->setIsDefault(false);
                    $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'type' => 'shipping']);
                    if (count($userAddresses) === 0) {
                        $address->setIsDefault(true);
                    }
                }
                $address->setIsDefaultBilling(false);
            }

            if (isset($data['type']) && $data['type'] === 'billing') {
                if (isset($data['isDefaultBilling']) && $data['isDefaultBilling']) {
                    $address->setIsDefaultBilling(true);
                    $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'isDefaultBilling' => true]);
                    foreach ($userAddresses as $existingAddress) {
                        $existingAddress->setIsDefaultBilling(false);
                    }
                } else {
                    $address->setIsDefaultBilling(false);
                    $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'type' => 'billing']);
                    if (count($userAddresses) === 0) {
                        $address->setIsDefaultBilling(true);
                    }
                }
                $address->setIsDefault(false);
            }

            $errors = $validator->validate($address);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $em->persist($address);
            $em->flush();

            return new JsonResponse([
                'message' => 'Adresse créée avec succès',
                'address' => [
                    'id' => $address->getId(),
                    'street' => $address->getStreet(),
                    'city' => $address->getCity(),
                    'postalCode' => $address->getPostalCode(),
                    'isDefault' => $address->isDefault(),
                    'isDefaultBilling' => $address->isDefaultBilling(),
                    'type' => $address->getType()
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la création de l\'adresse',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/addresses/{id}', name: 'addresses_put', methods: ['PUT'])]
    public function updateAddress(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $address = $em->getRepository(Address::class)->findOneBy(['id' => $id, 'user' => $user]);

            if (!$address) {
                return new JsonResponse(['error' => 'Adresse non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['street'])) {
                $address->setStreet($data['street']);
            }

            if (isset($data['city'])) {
                $address->setCity($data['city']);
            }

            if (isset($data['postalCode'])) {
                $address->setPostalCode($data['postalCode']);
            }

            if (isset($data['type'])) {
                $address->setType($data['type']);
            }

            if (isset($data['isDefault'])) {
                $isDefault = (bool) $data['isDefault'];
                $address->setIsDefault($isDefault);

                if ($isDefault) {
                    $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'isDefault' => true]);
                    foreach ($userAddresses as $existingAddress) {
                        if ($existingAddress->getId() !== $id) {
                            $existingAddress->setIsDefault(false);
                        }
                    }
                }
            }

            if (isset($data['isDefaultBilling'])) {
                $isDefaultBilling = (bool) $data['isDefaultBilling'];
                $address->setIsDefaultBilling($isDefaultBilling);

                if ($isDefaultBilling) {
                    $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'isDefaultBilling' => true]);
                    foreach ($userAddresses as $existingAddress) {
                        if ($existingAddress->getId() !== $id) {
                            $existingAddress->setIsDefaultBilling(false);
                        }
                    }
                }
            }

            $errors = $validator->validate($address);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Adresse mise à jour avec succès',
                'address' => [
                    'id' => $address->getId(),
                    'street' => $address->getStreet(),
                    'city' => $address->getCity(),
                    'postalCode' => $address->getPostalCode(),
                    'isDefault' => $address->isDefault(),
                    'isDefaultBilling' => $address->isDefaultBilling(),
                    'type' => $address->getType()
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la mise à jour de l\'adresse',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/addresses/{id}', name: 'addresses_delete', methods: ['DELETE'])]
    public function deleteAddress(
        int $id,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $address = $em->getRepository(Address::class)->findOneBy(['id' => $id, 'user' => $user]);

            if (!$address) {
                return new JsonResponse(['error' => 'Adresse non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $wasDefault = $address->isDefault();
            $wasDefaultBilling = $address->isDefaultBilling();

            $em->remove($address);
            $em->flush();

            if ($wasDefault) {
                $remainingAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'type' => 'shipping']);
                if (count($remainingAddresses) > 0) {
                    $remainingAddresses[0]->setIsDefault(true);
                    $em->flush();
                }
            }

            if ($wasDefaultBilling) {
                $remainingAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'type' => 'billing']);
                if (count($remainingAddresses) > 0) {
                    $remainingAddresses[0]->setIsDefaultBilling(true);
                    $em->flush();
                }
            }

            return new JsonResponse(['message' => 'Adresse supprimée avec succès']);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la suppression de l\'adresse',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/addresses/{id}/default', name: 'address_set_default', methods: ['PUT'])]
    public function setDefaultAddress(
        int $id,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $address = $em->getRepository(Address::class)->findOneBy(['id' => $id, 'user' => $user]);

            if (!$address) {
                return new JsonResponse(['error' => 'Adresse non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'type' => 'shipping']);
            foreach ($userAddresses as $existingAddress) {
                $existingAddress->setIsDefault($existingAddress->getId() === $id);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Adresse définie par défaut avec succès',
                'address' => [
                    'id' => $address->getId(),
                    'street' => $address->getStreet(),
                    'city' => $address->getCity(),
                    'postalCode' => $address->getPostalCode(),
                    'isDefault' => true,
                    'isDefaultBilling' => $address->isDefaultBilling(),
                    'type' => $address->getType()
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la définition de l\'adresse par défaut',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/addresses/{id}/default-billing', name: 'address_set_default_billing', methods: ['PUT'])]
    public function setDefaultBillingAddress(
        int $id,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $address = $em->getRepository(Address::class)->findOneBy(['id' => $id, 'user' => $user]);

            if (!$address) {
                return new JsonResponse(['error' => 'Adresse non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $userAddresses = $em->getRepository(Address::class)->findBy(['user' => $user, 'type' => 'billing']);
            foreach ($userAddresses as $existingAddress) {
                $existingAddress->setIsDefaultBilling($existingAddress->getId() === $id);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Adresse de facturation par défaut mise à jour',
                'address' => [
                    'id' => $address->getId(),
                    'street' => $address->getStreet(),
                    'city' => $address->getCity(),
                    'postalCode' => $address->getPostalCode(),
                    'isDefault' => $address->isDefault(),
                    'isDefaultBilling' => true,
                    'type' => $address->getType()
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la définition de l\'adresse de facturation par défaut',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}