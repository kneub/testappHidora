<?php

namespace Kneub\Services\Routing;

class Group
{
    private $name;
    private $resource = null;
    private $pattern = null;
    private $routes = null;
    private $middlewares = [];

    private $available_keys = ['name','pattern', 'middlewares', 'routes', 'resource'];

    public function __construct($groupArray = null)
    {
        if (is_array($groupArray)) {
            $this->initWithArray($groupArray);
        }
    }

    /**
     * init with a group array
     * @param  array  $group
     *
     */
    public function initWithArray(array $group)
    {
        // check keys
        $extract = array_diff(array_keys($group), $this->available_keys);
        if ($extract) {
            $errorKey = 'Erreur lors du chargement du group "%s", vérifier votre source de chargement.';
            throw new \Exception(sprintf($errorKey, $group['name']));
        }

        if (isset($group['resource'])) {
            $this->setResource($group['resource']);
        }
        if (isset($group['pattern'])) {
            $this->setPattern($group['pattern']);
        }
        if (isset($group['routes'])) {
            $this->setRoutes($group['routes']);
        }
        if (isset($group['middlewares']) && is_array($group['middlewares'])) {
            $this->setMiddlewares($group['middlewares']);
        }
    }


    /**
     * check if group is valide
     * @return boolean
     */
    public function validate()
    {
        $resource = $this->getResource();
        $routes = $this->getRoutes();

        if (!isset($routes) && !isset($resource)) {
            throw new \Exception(sprintf('Group (%s) require routes or resource.', $this->getName()));
        }

        if ((null == $resource && (!is_array($routes) || empty($routes)))
        ) {
            throw new \Exception(sprintf('Group (%s) require routes.', $this->getName()));
        }

        if (is_string($resource) && 5 >= strlen($resource)) { // x.yml)
            throw new \Exception(sprintf('Group (%s) require a resource .yml.', $this->getName()));
        }

        if (null === $this->getPattern()) {
            throw new \Exception(sprintf('Group (%s) require a pattern.', $this->getName()));
        }

        return true;
    }

    /**
     * Get group name
     * @return string name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set group name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return  $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return  $this;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setPattern($pattern)
    {
        $this->pattern = str_replace("//", "/", $pattern);
        if ('/' == $this->pattern) {
            $this->pattern = '';
        }
        return  $this;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRoutes($routes)
    {
        $this->routes = $routes;
        return $this;
    }

    public function addRoute($route)
    {
        $this->routes[] = $route;
        return  $this;
    }

    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    public function setMiddlewares($middlewares)
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;
        return  $this;
    }
}
