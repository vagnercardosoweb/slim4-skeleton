<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

use Core\Application;

// Auxiliary constants
$basePath = realpath(dirname(__DIR__));

if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    $basePath = realpath($_SERVER['DOCUMENT_ROOT']);
}

define('BASE_PATH', str_ireplace('\\', '/', $basePath));
define('APP_PATH', sprintf('%s/application', BASE_PATH));
define('CONFIG_PATH', sprintf('%s/config', APP_PATH));
define('ROUTE_PATH', sprintf('%s/routes', APP_PATH));
define('STORAGE_PATH', sprintf('%s/storage', APP_PATH));
define('RESOURCE_PATH', sprintf('%s/resources', APP_PATH));
define('PUBLIC_PATH', sprintf('%s/public_html', BASE_PATH));

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$schema = 'http';

if (
    (!empty($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS'])
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])
) {
    $schema = 'https';
}

define('BASE_URL', "{$schema}://{$host}");
define('REQUEST_URI', $_SERVER['REQUEST_URI'] ?? '/');

const FULL_URL = BASE_URL.REQUEST_URI;
const DATE_TIME_BR = 'd/m/Y H:i:s';
const E_USER_SUCCESS = 'success';
const DATE_BR = 'd/m/Y';

// Autoload.
$autoloadPath = sprintf('%s/vendor/autoload.php', APP_PATH);

if (!file_exists($autoloadPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => 'Run composer install']);
    exit;
}

require_once "{$autoloadPath}";

// Start application
try {
    (new Application(
        pathRoutes: ROUTE_PATH,
        pathMiddleware: sprintf('%s/middleware.php', CONFIG_PATH),
        pathProviders: sprintf('%s/providers.php', CONFIG_PATH),
        immutableEnv: true
    ))->run();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
    exit;
}
