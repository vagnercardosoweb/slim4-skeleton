<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/06/2021 Vagner Cardoso
 */

namespace App\Modules\Api\Controllers;

use Core\Controller;
use Core\Curl\Curl;
use Core\Support\Env;

class ZipCodeController extends Controller
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

        $response = Curl::get("https://viacep.com.br/ws/{$zipCode}/json")->send();
        $responseJson = $response->toJson();

        if (200 !== $response->getStatusCode() || !empty($responseJson->erro)) {
            throw new \Exception("O CEP {$zipCode} informado não foi encontrado, verifique e tente novamente.");
        }

        $responseJson->endereco = sprintf('%s - %s, %s - %s, %s, Brazil',
            $responseJson->logradouro,
            $responseJson->bairro,
            $responseJson->localidade,
            $responseJson->uf,
            $zipCode
        );

        if ($googleMapsKey = Env::get('GOOGLE_GEOCODE_API_KEY')) {
            $responseMap = Curl::get('https://maps.google.com/maps/api/geocode/json')
                ->setHeader('Content-Type', 'application/json')
                ->setBody([
                    'key' => $googleMapsKey,
                    'sensor' => true,
                    'address' => urlencode($responseJson->endereco),
                ])
                ->send()
            ;

            if (200 === $responseMap->getStatusCode()) {
                $jsonMap = $responseMap->toJson();

                if ('OK' === $jsonMap->status && !empty($jsonMap->results[0])) {
                    $location = $jsonMap->results[0]->geometry->location;
                    $responseJson->latitude = (string)$location->lat;
                    $responseJson->longitude = (string)$location->lng;
                }
            }
        }

        return $responseJson;
    }
}
