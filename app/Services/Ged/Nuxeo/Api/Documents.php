<?php


namespace Kneub\Services\Ged\Nuxeo\Api;

use Nuxeo\Client\Objects\Documents as DocumentsNuxeo;
use \Kneub\Services\Ged\Nuxeo\Client as NuxeoClient;

class Documents implements DocumentsInterface
{

    const OPERATION_REPOSITORY_QUERY = 'Repository.Query';
    const ROOT = "documents";

    private $client = null;

    public function __construct(NuxeoClient $client)
    {
        $this->client = $client;
    }


    public function getByTagNames (array $tags)  {

        $strTags = implode(',', $tags);

        return $this->client->get()->request('POST', $this->client->getUrl() . self::ROOT . "/tags/",[
            'form_params' => [
                'tags' => $strTags
            ]
        ]);
    }

    public function getCountByTagNames (array $tags)  {

        $strTags = implode(',', $tags);

        return $this->client->get()->request('POST', $this->client->getUrl() . self::ROOT . "/tags/count/",[
            'form_params' => [
                'tags' => $strTags
            ]
        ]);
    }

}
