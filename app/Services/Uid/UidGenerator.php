<?php

namespace Kneub\Services\Uid;

class UidGenerator
{
    private $client;
    private $url;

    public function __construct ($client, String $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function getUid()
    {
        return $this->client->request('GET', $this->url . 'uid/');
    }

}