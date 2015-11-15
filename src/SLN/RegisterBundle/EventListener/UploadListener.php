<?php
namespace SLN\RegisterBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Oneup\UploaderBundle\Event\PostUploadEvent;

use SLN\RegisterBundle\Entity\UploadFile;

class UploadListener
{
    protected $manager;
    protected $securityContext;

    /** @ignore **/
    public function __construct(EntityManager $manager, SecurityContext $securityContext) {
        $this->manager = $manager;
        $this->securityContext = $securityContext;
    }

    /** 
     * Called when a file is uploaded
     *
     * @param PostUploadEvent $event Event containing file information
     */
    public function onUpload(PostUploadEvent $event)
    {
        $request = $event->getRequest();
        $fileId = $request->get('file_id');
        $fileName = $request->get('name');
        $file = $event->getFile();

        $fileDir = date("Y-m-d");
        $outputDir = sprintf("%s/%s", UploadFile::UPLOADBASE, $fileDir);
        $file = $event->getFile()->move($outputDir);

        $uploadFile = new UploadFile($fileId);
        $uploadFile->setFilename($fileName);
        $uploadFile->setFilepath(sprintf("%s/%s", $fileDir, $file->getFilename()));
        $uploadFile->setUser($this->securityContext->getToken()->getUser());

        $this->manager->persist($uploadFile);
        $this->manager->flush();
    }
}
