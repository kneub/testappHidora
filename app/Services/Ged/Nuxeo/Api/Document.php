<?php


namespace Kneub\Services\Ged\Nuxeo\Api;

use Nuxeo\Client\Objects\Document as DocumentNuxeo;
use \Kneub\Services\Ged\Nuxeo\Client as ClientNuxeo;
use Nuxeo\Client\Objects\Blob\Blob as BlobNuxeo;
use Nuxeo\Client\Spi\NuxeoClientException;

class Document implements DocumentInterface
{

    protected const OPERATION_FETCH = 'Document.Fetch';
    protected const OPERATION_CREATE = 'Document.Create';
    protected const OPERATION_DELETE = 'Document.Delete';

    private $client = null;

    const ROOT = "documents";

    public function __construct(ClientNuxeo $client)
    {
        $this->client = $client;
    }

    
    public function getTest()
    {
        return $this->client->get()->request('GET', $this->client->getUrl() . self::ROOT . '/test/');
    }
    
    public function create(string $pathFile, string $dossier = null)
    {
        return $this->client->get()->request('POST', $this->client->getUrl() . self::ROOT . '/create/', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($pathFile, 'r')
                ],
                [
                    'name'     => 'folder',
                    'contents' => $dossier
                ]
            ]
        ]);
    }

    
    
    public function attachBlob($document, $blob)
    {   
        try {
            if (null !== $document) {
                $blobAdded = $this->client->automation('Blob.Attach')
                ->input($blob)
                ->param('document', $document->getPath())
                ->execute(BlobNuxeo::class);
                
                if(null === $blobAdded){
                    return ['error' => "Le fichier n'a pas pu être attaché au document"];
                }

                return $blobAdded;
            }else{
                return ['error' => 'Aucun document auquel ajouté un fichier']; 
            }
        }catch (NuxeoClientException  $ex){
            return new RuntimeException('Could not attach blob to document: ' . $ex->getMessage());
        }
    }
    public function delete($uid)
    {
        return $this->client->get()->request('DELETE', $this->client->getUrl() . self::ROOT . "/$uid/delete/");
    }

    

    public function get (string $uid)
    {
        return $this->client->get()->request('GET', $this->client->getUrl() . self::ROOT . "/$uid/");
    }

    public function getMeta (string $uid)
    {
        return $this->client->get()->request('GET', $this->client->getUrl() . self::ROOT . "/$uid/meta/");
    }

    public function getThumb(string $uid)
    {
        return $this->client->get()->request('GET', $this->client->getUrl() . self::ROOT . "/$uid/thumbnail/");
    }

    public function addTag($uid, $tag)
    {
        return $this->client->get()->request('POST', $this->client->getUrl() . self::ROOT . "/$uid/tag/add/",[
            'form_params' => [
                'name' => $tag
            ]
        ]);
    }

    public function removeTag($uid, $tag)
    {
        return $this->client->get()->request('DELETE', $this->client->getUrl() . self::ROOT . "/$uid/tag/remove/",[
            'form_params' => [
                'name' => $tag
            ]
        ]);
    }

    

    

}