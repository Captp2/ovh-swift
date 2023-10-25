<?php

namespace OvhSwift\Accessors\OVH\Getters;

use OvhSwift\Accessors\AbstractAccessor;
use OvhSwift\Entities\Authentication;
use OvhSwift\Entities\File;
use OvhSwift\Interfaces\API\Getters\IGetFiles;

class FileGetter extends AbstractAccessor implements IGetFiles
{
    public function getFileByName(Authentication $authentication, string $containerName, string $fileName): ?File
    {
        $request = $this->guzzleClient->request(
            'GET',
            $authentication->swiftUrl . "/{$containerName}",
            [
                'headers' => [
                    'X-Auth-Token' => $authentication->token,
                    'Accept' => 'application/json'
                ]
            ]);

        $files = json_decode($request->getBody()->getContents(), true);
        foreach ($files as $file) {
            if ($file['name'] === $fileName) {
                return new File([
                    'name' => $file['name'],
                    'path' => $authentication->swiftUrl . "/{$containerName}/" . $fileName,
                    'mimeType' => $file['content_type'],
                    'size'     => $file['bytes']
                ]);
            }
        }

        return null;
    }
}