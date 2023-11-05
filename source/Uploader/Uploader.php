<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

namespace Core\Uploader;

use Core\Exception\UploaderException;

/**
 * Class Uploader.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Uploader
{
    /**
     * @var array
     */
    protected array $allowedMimeTypes = [];

    /**
     * @var array
     */
    protected array $allowedExtensions = [];

    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var string
     */
    protected string $extension;

    /**
     * @var string
     */
    protected string $uploadDir;

    /**
     * @var array
     */
    protected array $uploadFile;

    /**
     * Uploader constructor.
     *
     * @param string|null $uploadDir
     * @param array|null  $uploadFile
     *
     * @throws \Exception
     */
    public function __construct(
        string $uploadDir = null,
        array $uploadFile = null,
    ) {
        if ($uploadDir) {
            $this->setUploadDir($uploadDir);
        }

        if ($uploadFile) {
            $this->setUploadFile($uploadFile);
        }
    }

    /**
     * @return string
     */
    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }

    /**
     * @param string $uploadDir
     *
     * @return Uploader
     */
    public function setUploadDir(string $uploadDir): Uploader
    {
        $this->uploadDir = $uploadDir;
        $this->validateUploadDir();

        return $this;
    }

    /**
     * @param array $uploadedFile
     *
     * @throws \Exception
     *
     * @return Uploader
     */
    public function setUploadFile(array $uploadedFile): Uploader
    {
        if (empty($uploadedFile)) {
            return $this;
        }

        $this->uploadFile = $uploadedFile;
        $this->validateUploadFile();

        $this->filename = bin2hex(random_bytes(16));
        $this->extension = mb_strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * @param string[] $mimeTypes
     *
     * @return Uploader
     */
    public function setAllowedMimeTypes(array $mimeTypes): Uploader
    {
        $this->allowedMimeTypes = array_merge($this->allowedMimeTypes, $mimeTypes);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * @param string[] $extensions
     *
     * @return Uploader
     */
    public function setAllowedExtensions(array $extensions): Uploader
    {
        $this->allowedExtensions = array_merge($this->allowedExtensions, $extensions);

        return $this;
    }

    /**
     * @param string $extension
     *
     * @return Uploader
     */
    public function setExtension(string $extension): Uploader
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function setFilename(string $filename): Uploader
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @throws \Exception
     */
    public function save(): string
    {
        $this->validateUploadFile();
        $this->validateMimeTypes();
        $this->validateExtensions();

        $filename = $this->getFilename();
        $extension = $this->getExtension();

        $uploadFileDir = sprintf('%s%s%s.%s',
            $this->getUploadDir(),
            DIRECTORY_SEPARATOR,
            $filename,
            $extension
        );

        $this->moveUploadedFile($uploadFileDir);

        return sprintf('%s.%s', $filename, $extension);
    }

    /**
     * @param array $uploadedFiles
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function normalizeMultipleFiles(array $uploadedFiles): array
    {
        $parsedFiles = [];

        foreach ($uploadedFiles as $fieldName => $uploadedFile) {
            if (!is_array($uploadedFile['name'])) {
                $parsedFiles[$fieldName] = $uploadedFile;
                continue;
            }

            $fileKeys = array_keys($uploadedFile);

            foreach ($uploadedFile['error'] as $key => $error) {
                if (UPLOAD_ERR_OK !== $error) {
                    throw new UploaderException($error);
                }

                foreach ($fileKeys as $fileKey) {
                    $parsedFiles[$fieldName][$key][$fileKey] = $uploadedFile[$fileKey][$key];
                }
            }
        }

        return $parsedFiles;
    }

    /**
     * @return float|int
     */
    public static function getMaxFilesize(): float|int
    {
        $config = ini_get('upload_max_filesize');
        $newSize = 0;
        $pow = fn (int $exponent) => pow(1024, $exponent);

        if (preg_match('/([0-9]+)+([a-zA-Z]+)/', $config, $matches)) {
            $bytes = $matches[1];
            $size = $matches[2];

            $newSize = match ($size) {
                'K', 'KB' => ($bytes * $pow(1)),
                'M', 'MB' => ($bytes * $pow(2)),
                'G', 'GB' => ($bytes * $pow(3)),
                'T', 'TB' => ($bytes * $pow(4)),
                'P', 'PB' => ($bytes * $pow(5)),
            };
        }

        return $newSize;
    }

    /**
     * @return void
     */
    protected function validateUploadDir(): void
    {
        if (!file_exists($this->uploadDir) || !is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    protected function validateUploadFile(): void
    {
        if (empty($this->uploadFile['type'])) {
            throw new \InvalidArgumentException('Upload file not exists.');
        }

        if (!empty($this->uploadFile['error'])) {
            throw new UploaderException($this->uploadFile['error']);
        }
    }

    /**
     * @return void
     */
    protected function validateMimeTypes(): void
    {
        if (empty($this->getAllowedMimeTypes())) {
            return;
        }

        if (!in_array($this->uploadFile['type'], $this->getAllowedMimeTypes())) {
            throw new \InvalidArgumentException(
                "Media type `{$this->uploadFile['type']}` not allowed in upload."
            );
        }
    }

    /**
     * @return void
     */
    protected function validateExtensions(): void
    {
        if (!in_array($this->getExtension(), $this->getAllowedExtensions())) {
            throw new \InvalidArgumentException(
                "Extension `{$this->getExtension()}` not allowed for upload."
            );
        }
    }

    /**
     * @param string $uploadFileDir
     */
    protected function moveUploadedFile(string $uploadFileDir): void
    {
        if (!move_uploaded_file($this->uploadFile['tmp_name'], $uploadFileDir)) {
            throw new \RuntimeException(sprintf(
                'Error moving uploaded file %s to %s',
                $this->uploadFile['name'],
                $uploadFileDir
            ));
        }
    }
}
