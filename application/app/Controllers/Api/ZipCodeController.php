<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use Core\Curl\Curl;
use Core\Support\Env;

class ZipCodeController extends BaseController
{
    /**
     * @param string $zipCode
     *
     * @throws \Exception
     *
     * @return object
     */
    public function index(string $zipCode): object
    {
        $zipCode = preg_replace('/[^0-9]/', '', $zipCode);

        if (strlen($zipCode) < 8) {
            throw new \InvalidArgumentException("O CEP {$zipCode} informado deve conter, no mínimo 8 números.");
        }

        $response = $this->container->get(Curl::class)->get("https://viacep.com.br/ws/{$zipCode}/json");
        $body = $response->toJson();

        if (200 !== $response->getStatusCode() || !empty($body->erro)) {
            throw new \Exception("O CEP {$zipCode} informado não foi encontrado, verifique e tente novamente.");
        }

        $body->endereco = sprintf('%s - %s, %s - %s, %s, Brazil',
            $body->logradouro,
            $body->bairro,
            $body->localidade,
            $body->uf,
            $zipCode
        );

        if ($googleMapsKey = Env::get('GOOGLE_GEOCODE_API_KEY', null)) {
            $responseMap = $this->container->get(Curl::class)
                ->setHeaders('Content-Type', 'application/json')
                ->get('https://maps.google.com/maps/api/geocode/json', json_encode([
                    'key' => $googleMapsKey,
                    'sensor' => true,
                    'address' => urlencode($body->endereco),
                ]))
            ;

            if (200 === $responseMap->getStatusCode()) {
                $jsonMap = $responseMap->toJson();

                if ('OK' === $jsonMap->status && !empty($jsonMap->results[0])) {
                    $location = $jsonMap->results[0]->geometry->location;
                    $body->latitude = (string)$location->lat;
                    $body->longitude = (string)$location->lng;
                }
            }
        }

        return $body;
    }
}
