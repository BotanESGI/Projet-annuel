<?php

namespace App\EventListener;

use App\Entity\User;
use App\Exception\UnverifiedUserException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckVerifiedUserListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'onCheckPassport',
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        $user = $passport->getUser();

        if ($user instanceof User && !$user->getIsVerified()) {
            throw new UnverifiedUserException();
        }
    }
}
