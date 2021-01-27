<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/01/2021 Vagner Cardoso
 */

use Core\App;

// Autoload.
$autoload = APP_PATH.'/vendor/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => 'Run composer install']);
    exit;
}

require_once "{$autoload}";

$app = new App();
$app->registerMiddleware();
$app->registerFolderRoutes();
$app->run();
