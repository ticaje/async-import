<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportService\Model\Source\Validator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\File\Mime\Proxy as Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Driver\Http\Proxy as Http;
use Magento\Framework\Filesystem\Io\File;
use Magento\ImportServiceApi\Api\SourceBuilderInterface;
use Magento\ImportService\Model\Import\SourceTypePool;

/**
 * Class MimeTypeValidator
 */
class MimeTypeValidator implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\Http
     */
    private $httpDriver;

    /**
     * @var SourceTypePool
     */
    private $sourceTypePool;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var File
     */
    private $fileSystemIo;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @param Http $httpDriver
     * @param SourceTypePool $sourceTypePool
     * @param Mime $mime
     * @param File $fileSystemIo
     * @param Filesystem $fileSystem
     */
    public function __construct(
        Http $httpDriver,
        SourceTypePool $sourceTypePool,
        Mime $mime,
        File $fileSystemIo,
        Filesystem $fileSystem
    ) {
        $this->httpDriver = $httpDriver;
        $this->sourceTypePool = $sourceTypePool;
        $this->mime = $mime;
        $this->fileSystemIo = $fileSystemIo;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Return error messages in array
     *
     * @param SourceBuilderInterface $source
     *
     * @return array
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function validate(SourceBuilderInterface $source)
    {
        $errors = [];

        /** @var $mimeType */
        $mimeType = false;

        /** validate import source for remote url or local path */
        if (filter_var($source->getImportData(), FILTER_VALIDATE_URL)) {
            /** check empty variable */
            $importData = $source->getImportData();

            if (isset($importData) && $importData !== '') {
                $externalSourceUrl = preg_replace('(^https?://)', '', $importData);

                /** check for file exists */
                if ($this->httpDriver->isExists($externalSourceUrl)) {
                    /** @var array $stat */
                    $stat = $this->httpDriver->stat($externalSourceUrl);
                    if (isset($stat['type'])) {
                        $mimeType = $stat['type'];
                    }
                }
            }
        } else {
            /** @var Write $write */
            $write = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);

            /** create absolute path */
            $absoluteFilePath = $write->getAbsolutePath($source->getImportData());

            /** check if file exist */
            if ($this->fileSystemIo->fileExists($absoluteFilePath)) {
                $mimeType = $this->mime->getMimeType($absoluteFilePath);
            }
        }

        if ($mimeType) {
            if (is_array($mimeType)){
                $mimeType = implode(";", $mimeType);
            }
            $mimeType = trim(explode(";", $mimeType)[0]);
            if (!in_array($mimeType, $this->sourceTypePool->getAllowedMimeTypes())) {
                $errors[] = __(
                    'Invalid mime type: %1, expected is one of: %2',
                    $mimeType,
                    implode(', ', $this->sourceTypePool->getAllowedMimeTypes())
                );
            }
        }

        return $errors;
    }
}
