<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace App\Controllers;

use Core\Controller;
use Core\Curl\Curl;
use Core\Facades\Cache;
use Core\Support\Env;
use Fig\Http\Message\StatusCodeInterface;

class ZipCodeController extends Controller
{
    public function index(string $zipCode): array
    {
        $zipCode = preg_replace('/[^0-9]/', '', $zipCode);

        if (strlen($zipCode) < 8) {
            throw new \InvalidArgumentException("O CEP {$zipCode} informado deve conter, no mínimo 8 números.");
        }

        $result = Cache::get("zip-code:{$zipCode}:results", function () use ($zipCode) {
            $response = Curl::get("https://viacep.com.br/ws/{$zipCode}/json")->send();

            if (200 !== $response->getStatusCode()) {
                throw new \DomainException("O CEP {$zipCode} informado não foi encontrado, verifique e tente novamente.");
            }

            $resultViaCep = $response->toArray();
            $resultViaCep['endereco'] = sprintf('%s - %s, %s - %s, %s, Brazil',
                $resultViaCep['logradouro'],
                $resultViaCep['bairro'],
                $resultViaCep['localidade'],
                $resultViaCep['uf'],
                $zipCode
            );

            if ($googleMapsKey = Env::get('GOOGLE_GEOCODE_API_KEY')) {
                $responseMap = Curl::get('https://maps.google.com/maps/api/geocode/json')
                    ->addHeader('Content-Type', 'application/json')
                    ->addBody([
                        'key' => $googleMapsKey,
                        'sensor' => true,
                        'address' => urlencode($resultViaCep['endereco']),
                    ])
                    ->send()
                ;

                if (200 === $responseMap->getStatusCode()) {
                    $jsonMap = $responseMap->toArray();

                    if ('OK' === $jsonMap['status'] && !empty($jsonMap['results'][0])) {
                        $location = $jsonMap['results'][0]['geometry']['location'];
                        $resultViaCep['latitude'] = $location['lat'];
                        $resultViaCep['longitude'] = $location['lng'];
                    }
                }
            }

            return $resultViaCep;
        });

        return [
            'data' => $result,
            'statusCode' => StatusCodeInterface::STATUS_OK,
            'metadata' => ['zipCode' => $zipCode],
        ];
    }
}
