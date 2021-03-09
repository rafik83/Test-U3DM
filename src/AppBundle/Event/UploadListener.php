<?php
namespace AppBundle\Event;

use Oneup\UploaderBundle\Event\PostPersistEvent;
use AppBundle\Entity\PrintFile;

class UploadListener
{

    private $em;

    public function __construct( $em)
    {
        $this->em = $em;
    }
    
    public function onUpload(PostPersistEvent $event)
    {
        
        try {

            $file = $event->getFile();


            $request = $event->getRequest();
            $response = $event->getResponse();
            //$response['success'] = true;
            $response['file_name'] = $file->getFileName();
            //$response['file_path'] = $file->getPathName();
            //$response['data'] = $request->get('example');

            $printFile = new PrintFile();
            $printFile->setName($response['file_name']);
            $this->em->persist($printFile);
            $this->em->flush();

            $response['file_db'] = $printFile->getId();

            return $response;
            
        } catch (Exception $e) {

            return $e;
            
        }

    }
}