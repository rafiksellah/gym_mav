<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private readonly string $uploadsDirectory,
        private readonly SluggerInterface $slugger,
    ) {
    }

    /**
     * @throws FileException
     */
    public function upload(UploadedFile $file, string $subDirectory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename)->lower();
        $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid(), $file->guessExtension());

        $targetDirectory = rtrim($this->uploadsDirectory, '/').'/'.$subDirectory;
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $file->move($targetDirectory, $newFilename);

        return $newFilename;
    }

    public function remove(?string $filename, string $subDirectory): void
    {
        if (!$filename) {
            return;
        }

        $path = rtrim($this->uploadsDirectory, '/').'/'.$subDirectory.'/'.$filename;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
