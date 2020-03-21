<?php

namespace Kneub\Services\Parser;

use Symfony\Component\Yaml\Yaml;
use Kneub\Services\Parser\ParserInterface;

class ParserYaml implements ParserInterface
{

    private $yaml;

    public function __construct()
    {
        $this->yaml = null;
    }

    public function load($file)
    {
        if (file_exists($file)) {
            $this->yaml = Yaml::parse(file_get_contents($file));
        }

        return $this;
    }

    public function getParams($key)
    {
        if (isset($this->yaml, $this->yaml[$key])) {
            return $this->yaml[$key];
        }

        return null;
    }

    public function getArray()
    {
        return $this->yaml;
    }
}
