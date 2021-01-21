<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 21/01/2021 Vagner Cardoso
 */

$basePath = realpath(dirname(__DIR__));

if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    $basePath = realpath($_SERVER['DOCUMENT_ROOT']);
}

define('BASE_PATH', str_ireplace('\\', '/', $basePath));
define('APP_PATH', sprintf('%s/application', BASE_PATH));
define('CONFIG_PATH', sprintf('%s/config', APP_PATH));
define('STORAGE_PATH', sprintf('%s/storage', APP_PATH));
define('RESOURCE_PATH', sprintf('%s/resources', APP_PATH));
define('PUBLIC_PATH', sprintf('%s/public_html', BASE_PATH));

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$schema = 'http';

if (
    (!empty($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])
) {
    $schema = 'https';
}

define('BASE_URL', "{$schema}://{$host}");

$bootstrapPath = sprintf('%s/app/bootstrap.php', APP_PATH);

require_once "{$bootstrapPath}";
