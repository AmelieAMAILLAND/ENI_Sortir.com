<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCoordinates(string $adress): ?array
    {
        $response = $this->client->request(
            'GET',
            "https://nominatim.openstreetmap.org/search?q=$adress&addressdetails=1&format=json"
        );
        $element = json_decode($response->getContent(), true);
        if (!$element){
            return null;
        }
        return ['lat'=>$element[0]['lat'],'lon'=>$element[0]['lon']];
    }


}