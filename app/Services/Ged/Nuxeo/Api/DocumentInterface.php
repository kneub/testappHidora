<?php

namespace Kneub\Services\Ged\Nuxeo\Api;

interface DocumentInterface
{
    public function create(string $pathFile, string $dossier = null);

    public function delete($document);

    public function attachBlob($document, $blob);

    public function addTag($id, $tag);

    public function removeTag($document, $tag);

    public function getThumb(string $uid);

    public function get(string $uid);
}