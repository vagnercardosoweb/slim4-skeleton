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

/**
 * Class MediaUploader.
 */
class MediaUploader extends Uploader
{
    /**
     * @var array
     */
    protected array $allowedMimeTypes = [
        'audio/mp3',
        'audio/mpeg',
        'video/mp4',
    ];

    /**
     * @var array
     */
    protected array $allowedExtensions = [
        'mp3',
        'mp3',
    ];
}
