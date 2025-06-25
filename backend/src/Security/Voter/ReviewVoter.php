<?php

namespace App\Security\Voter;

use App\Entity\Review;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReviewVoter extends Voter
{
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::DELETE && $subject instanceof Review;
    }

    protected function voteOnAttribute(string $attribute, $review, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $review->getUser() && $review->getUser()->getId() === $user->getId();
    }
}