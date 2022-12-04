<?php

namespace app\services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DbService
{
    public function __construct(
        public readonly string $url,
        private readonly string $api,
        private readonly string $objectClass,
        private Client $client,
    )
    {
    }

    public function get(): array
    {
        $response = $this->client->get($this->url, ['headers' => $this->getHeaders()]);
        return [];
    }

    /**
     * @throws GuzzleException
     */
    public function append($data)
    {
        if (!($data instanceof $this->objectClass)) {
            throw new \InvalidArgumentException(get_class($data) . ' is not ' . $this->objectClass);
        }

        $response = $this->client->post(
            $this->url, [
                'headers' => $this->getHeaders(),
                'body'    => json_encode($data, JSON_THROW_ON_ERROR),
            ]);
    }

    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-apikey' => $this->api,
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

}
