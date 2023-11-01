<?php

namespace OvhSwift\Domains;

use OvhSwift\Accessors\OVH\Setters\FileSetter;
use OvhSwift\Entities\File;
use OvhSwift\Exceptions\OpenStackException;
use OvhSwift\Exceptions\RessourceNotFoundException;
use OvhSwift\Exceptions\RessourceValidationException;
use OvhSwift\Interfaces\API\Getters\IGetFiles;
use OvhSwift\Interfaces\API\Setters\ISetFiles;
use OvhSwift\Interfaces\SPI\IUseFiles;

class FileManager extends AbstractDomain
{
    /**
     * @var IUseFiles $spiAdapter
     */
    protected object $spiAdapter;

    /**
     * @var FileSetter
     */
    protected object $setter;

    /**
     * @param string $fileName
     * @return mixed
     * @throws RessourceNotFoundException
     */
    public function findByName(string $containerName, string $fileName): File
    {
        if (!$file = $this->getter->getFileByName($containerName, $fileName)) {
            throw new RessourceNotFoundException("File {$fileName} not found in {$containerName}");
        }

        return $file;
    }

    /**
     * @param File $file
     * @return void
     * @throws RessourceValidationException
     */
    public function uploadFile(string $containerName, File $file): void
    {
        if (!$this->spiAdapter->validateFileName($file->name)) {
            throw new RessourceValidationException("Filename {$file->name} is invalid");
        }
        if (!$this->spiAdapter->validateMimeType($file->mimeType)) {
            throw new RessourceValidationException("Filetype {$file->mimeType} is invalid");
        }
        if (!$this->spiAdapter->validateFileSize($file->size)) {
            throw new RessourceValidationException("Filesize is invalid");
        }

        try {
            $this->setter->uploadFile($containerName, $file->name, $file->path);
        } catch (\Exception $e) {
            throw new OpenStackException($e->getMessage());
        }
    }

    /**
     * @param string $containerName
     * @param string $fileName
     * @return bool
     * @throws OpenStackException
     * @throws RessourceNotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteFile(string $containerName, string $fileName): bool
    {
        $response = $this->setter->deleteFile($containerName, $fileName);
        ray($response);
        if (!$response->success) {
            ray($response->errors);
            if (isset($response->errors['404'])) {
                ray('yo');
                throw new RessourceNotFoundException($response->errors['404']);
            }

            throw new OpenStackException($response->errors['code']);
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getterInterface(): string
    {
        return IGetFiles::class;
    }

    /**
     * @return string
     */
    protected function setterInterface(): string
    {
        return ISetFiles::class;
    }
}