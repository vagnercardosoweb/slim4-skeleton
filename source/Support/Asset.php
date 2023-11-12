<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Asset.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Asset
{
    /**
     * @param string      $path
     * @param string|null $baseUrl
     * @param bool        $version
     *
     * @return string|null
     */
    public static function path(string $path, string $baseUrl = null, bool $version = true): ?string
    {
        $fullPath = Path::public_html($path);

        if (!file_exists($fullPath)) {
            return null;
        }

        $hash = $version ? '?v='.substr(md5_file($fullPath), 0, 15) : '';
        $baseUrl = $baseUrl ?? defined('BASE_URL') ? BASE_URL : '';

        return "{$baseUrl}{$path}{$hash}";
    }

    /**
     * @param array $files
     *
     * @return string|null
     */
    public static function source(array $files): ?string
    {
        $contents = [];

        foreach ($files as $file) {
            $file = Path::public_html($file);

            if (file_exists($file)) {
                $contents[] = file_get_contents($file);
            }
        }

        return implode('', $contents);
    }
}
