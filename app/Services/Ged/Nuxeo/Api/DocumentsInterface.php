<?php

namespace Kneub\Services\Ged\Nuxeo\Api;

interface DocumentsInterface
{
    public function getByTagNames (array $tags);

    public function getCountByTagNames (array $tags);
}