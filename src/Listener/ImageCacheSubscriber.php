<?php

namespace App\Listener;

use phpDocumentor\Reflection\DocBlock\Tags\Property;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use App\Entity\Tip;
use Vich\UploaderBundle\Handler\UploadHandler;

class ImageCacheSubscriber implements EventSubscriber
{

    private $cacheManager;
    private $uploaderHelper;
    private $handler;

    public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper, UploadHandler $uploadHandler)
    {
        $this->handler = $uploadHandler;
        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove => "preRemove",
            Events::preUpdate => "preUpdate",
            Events::postPersist => "postPersist"
        ];
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        // TODO : Récupérer l'image uploadée, la compresser, réduire de taille et enregistrer au même chemin. 
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->removeCacheImage($eventArgs);
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $this->removeCacheImage($eventArgs);
    }

    public function removeCacheImage(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        try {
            $imageFile = $entity->getImageFile();
        } catch (\Throwable $th) {
            return;
        }
        if ($entity instanceof Property) {
            return;
        }
        if ($imageFile instanceof UploadedFile && $entity instanceof Tip) {
            $path  = $this->uploaderHelper->asset($entity, "imageFile");
            if (!is_null($path)) {
                $this->cacheManager->remove($this->uploaderHelper->asset($entity, "imageFile", Tip::class));
                $this->handler->remove($entity, 'imageFile');
            } else {
                return;
            }
        }
    }
}
