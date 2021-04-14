<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Core\Exception;

/**
 * Class UploaderException.
 */
class UploaderException extends \Exception
{
    /**
     * UploaderException constructor.
     *
     * @param int $code
     */
    public function __construct(int $code)
    {
        $message = match ($code) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the limit defined in the php.ini `upload_max_filesize` directive.',
            UPLOAD_ERR_FORM_SIZE => 'The file exceeds the limit defined in `MAX_FILE_SIZE` in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The file was partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No files were uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write the file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped uploading the file.',
            default => 'Unknown upload error'
        };

        parent::__construct($message, $code, $this->getPrevious());
    }
}
