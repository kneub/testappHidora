<?php

namespace Kneub\Services\Ged\Nuxeo;

class Client
{
    private $client;
    private $url;

    public function __construct ($client, String $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function get()
    {
        return $this->client;
    }

    public function getContextAuth()
    {
        /*$auth = base64_encode("{$this->username}:{$this->password}");
        return stream_context_create([
            "http" => [
                "header" => "Authorization: Basic $auth"
            ]
        ]);*/
    }
}