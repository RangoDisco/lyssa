<?php

namespace App\Service;

use App\Entity\Media;
use http\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class Uploader
{

    const int MAX_FILE_SIZE = 1024 * 10000;
    const array VALID_EXTENSIONS = ['jpeg', 'jpg', 'png'];

    public function __construct(
        private SluggerInterface                                          $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads')] private string $uploadDir,
    )
    {
    }

    /**
     * @throws \InvalidArgumentException
     * @throws FileException
     */
    public function handleFile(UploadedFile $file): Media
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid('', true) . '.' . $file->guessExtension();

        if (!$this->isValid($file)) {
            throw new InvalidArgumentException("File is not valid.");
        }

        $file->move($this->uploadDir, $newFilename);

        return new Media()
            ->setImageName($newFilename);
    }

    private function isValid(UploadedFile $file): bool
    {

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return false;
        }

        if (!in_array($file->guessExtension(), self::VALID_EXTENSIONS, true)) {
            return false;
        }

        return true;
    }
}
