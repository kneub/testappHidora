<?php

namespace Kneub\Services\Routing;

class Route
{
    private $name;
    private $pattern = null;
    private $controller;
    private $action;
    private $methode = 'GET';
    private $middlewares = [];

    private $available_keys = ['name','pattern','controller','action','methode','conditions','middlewares', 'group'];
    private $available_methodes = ['GET', 'POST','PUT', 'DELETE', 'OPTIONS', 'HEAD'];


    public function __construct($routeArray = null)
    {
        if (is_array($routeArray)) {
            $this->initWithArray($routeArray);
        }
    }

    /**
     * init with a route array
     * @param  array  $route
     *
     */
    public function initWithArray(array $route)
    {
        // check keys
        $extract = array_diff(array_keys($route), $this->available_keys);
        if ($extract) {
            $errorKey = 'Erreur lors du chargement de la route  "%s", vérifier votre source de chargement.';
            throw new \Exception(sprintf($errorKey, $route['name']));
        }

        if (isset($route['name'])) {
            $this->setName($route['name']);
        }
        if (isset($route['pattern'])) {
            $this->setPattern($route['pattern']);
        }
        if (isset($route['controller'])) {
            $this->setController($route['controller']);
        }
        if (isset($route['action'])) {
            $this->setAction($route['action']);
        }
        if (isset($route['methode'])) {
            $this->setMethode($route['methode']);
        }
        if (isset($route['middlewares']) && is_array($route['middlewares'])) {
            $this->setMiddlewares($route['middlewares']);
        }
    }


    /**
     * check if route is valide
     * @return boolean
     */
    public function validate()
    {
        if (0 == strlen($this->getName())) {
            throw new \Exception(sprintf('Route require a name.'));
        }

        if (null === $this->getPattern()) {
            throw new \Exception(sprintf('Route "%s" require a pattern.', $this->getName()));
        }

        if (0 == strlen($this->getController())) {
            throw new \Exception(sprintf('Route "%s" require a controller.', $this->getName()));
        }

        if (0 == strlen($this->getAction())) {
            throw new \Exception(sprintf('Route "%s" require an action.', $this->getName()));
        }

        if (is_array($this->getMethode()) &&
                count($this->getMethode()) != count(array_intersect($this->getMethode(), $this->available_methodes))) {
            throw new \Exception(sprintf('Route "%s" require a valid method.', $this->getName()));
        }

        return true;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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

    public function getController()
    {
        return $this->controller;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        return  $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return  $this;
    }

    public function getMethode()
    {
        return $this->methodes;
    }

    public function setMethode($methode)
    {
        // Converte to an array
        if (!is_array($methode)) {
            $methode = [$methode];
        }
        $this->methodes = array_map('strtoupper', $methode);
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
