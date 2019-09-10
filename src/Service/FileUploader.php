<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{

    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file)
    {
        $filename = md5(uniqid()) . '.' . $file->guessExtension();
        try {
            $file->move($this->getTargetDirectory(), $filename);
        } catch (FileException $e) {
            return null;
        }
        return $filename;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
