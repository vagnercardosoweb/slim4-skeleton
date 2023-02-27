<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace Core\Uploader;

/**
 * Class ImageUploader.
 */
class ImageUploader extends Uploader
{
    /**
     * @var string[]
     */
    protected array $allowedExtensions = [
        'jpg',
        'jpeg',
        'gif',
        'png',
    ];

    /**
     * @var string[]
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];

    /**
     * @var int
     */
    protected int $width;

    /**
     * @var int
     */
    protected int $height;

    /**
     * @throws \Exception
     */
    public function save(): string
    {
        $this->validateUploadFile();
        $this->validateMimeTypes();
        $this->validateExtensions();

        return $this->saveImage();
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return ImageUploader
     */
    public function setWidth(int $width): ImageUploader
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     *
     * @return ImageUploader
     */
    public function setHeight(int $height): ImageUploader
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @param mixed $image
     *
     * @return resource|bool
     */
    private function fixRotate(mixed $image)
    {
        if (
            in_array($this->getExtension(), ['jpg', 'jpeg', 'tiff'])
            && function_exists('exif_read_data')
            && is_resource($image)
        ) {
            $exif = exif_read_data($this->uploadFile['tmp_name']);
            $orientation = !empty($exif['Orientation']) ? $exif['Orientation'] : null;

            $image = match ($orientation) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };
        }

        return $image;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    private function saveImage(): string
    {
        list($imageWidth, $imageHeight) = getimagesize($this->uploadFile['tmp_name']);

        if (!empty($this->width) && empty($this->height)) {
            $newImageWidth = $this->width < $imageWidth ? $this->width : $imageWidth;
            $newImageHeight = ($newImageWidth * $imageHeight) / $imageWidth;
        } elseif (!empty($this->width) && !empty($this->height)) {
            $ratioImage = $imageWidth / $imageHeight;

            if (($this->width / $this->height) > $ratioImage) {
                $newImageWidth = $this->height * $ratioImage;
                $newImageHeight = $this->height;
            } else {
                $newImageHeight = $this->width / $ratioImage;
                $newImageWidth = $this->width;
            }
        } else {
            $newImageWidth = $imageWidth;
            $newImageHeight = $imageHeight;
        }

        $dstImage = imagecreatetruecolor($newImageWidth, $newImageHeight);

        if ('image/jpeg' === $this->uploadFile['type']) {
            $srcImage = imagecreatefromjpeg($this->uploadFile['tmp_name']);
            $srcImage = $this->fixRotate($srcImage);
            $this->extension = 'jpg';
        } elseif ('image/gif' === $this->uploadFile['type']) {
            $srcImage = imagecreatefromgif($this->uploadFile['tmp_name']);
            imagecolortransparent($dstImage, imagecolorallocatealpha($dstImage, 0, 0, 0, 127));
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
        } else {
            $srcImage = imagecreatefrompng($this->uploadFile['tmp_name']);
            imagecolortransparent($dstImage, imagecolorallocatealpha($dstImage, 0, 0, 0, 127));
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
        }

        imagecopyresampled(
            $dstImage,
            $srcImage,
            0,
            0,
            0,
            0,
            $newImageWidth,
            $newImageHeight,
            $imageWidth,
            $imageHeight
        );

        $filename = $this->getFilename();
        $extension = $this->getExtension();
        $uploadFileDir = sprintf('%s%s%s.%s',
            $this->getUploadDir(),
            DIRECTORY_SEPARATOR,
            $filename,
            $extension
        );

        if ('image/jpeg' === $this->uploadFile['type']) {
            imagejpeg($dstImage, $uploadFileDir);
        } elseif ('image/gif' === $this->uploadFile['type']) {
            imagegif($dstImage, $uploadFileDir);
        } else {
            imagepng($dstImage, $uploadFileDir);
        }

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return sprintf('%s.%s', $filename, $extension);
    }
}
