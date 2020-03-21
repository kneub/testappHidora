<?php

namespace Kneub\Services\Parser;

use Symfony\Component\Yaml\Yaml;

interface ParserInterface
{
    public function load($file);
}
