<?php

namespace App\EventSubscriber;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;

class EasyAdminSlugSubscriber implements EventSubscriberInterface
{
    public function __construct(protected SluggerInterface $slugger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'setSlug',
            BeforeEntityUpdatedEvent::class => 'setSlug',
        ];
    }

    public function setSlug(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (method_exists($entity, 'getName')) {
            $name = $entity->getName();
            $slug = $this->slugger->slug($name)->lower();
            $firstLetter = ucfirst($name);

            $entity->setSlug($slug)
                ->setName($firstLetter);

        }
    }
}