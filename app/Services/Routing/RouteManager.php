<?php

namespace Kneub\Services\Routing;

use \Kneub\Services\Routing\Route;
use \Kneub\Services\Routing\Group;
use \Kneub\Services\Parser\ParserInterface as Parser;

class RouteManager
{
    private $app;
    private $parser;
    private $params;

    public function __construct($app, Parser $parser, $params)
    {
        $this->app = $app;
        $this->parser = $parser;
        $this->params = $params;
    }

    public function load($data)
    {
        try {
            if (is_array($data)) {
                $this->loadFromArray($data);
            } else {
                $this->loadFromFile($data);
            }
        } catch (\Exception $e) {
            echo "Load error : ({$e->getMessage()})";
            //die();
        }
    }

    // load all routes from file
    private function loadFromFile($url)
    {
        if (!stream_is_local($url)) {
            throw new \Exception(sprintf('File "%s" does not local .', $url));
        }

        if (!file_exists($url)) {
            throw new \Exception(sprintf('File "%s" does not exist.', $url));
        }

        $routes = $this->parser->load($url);
        $this->loadFromArray($routes->getArray()['routes']);
    }

    // load all routes from array
    private function loadFromArray($routes)
    {
        if (!isset($routes) || !is_array($routes) || empty($routes)) {
            throw new \Exception(sprintf("Routes is require and must be not empty"));
        }

        foreach ($routes as $name => $routearray) {
            // if  it's a resource load target
            if (isset($routearray['resource'])) {
                $proj = $this->app->getContainer()->get('params.projPath');
                $this->loadFromFile($proj."/".$routearray['resource']);
            } else {
                $this->loadRoute($name, $routearray);
            }
        }
    }

    private function loadGroup($name, $groupArray)
    {
        $arrayGroup['name'] = $name;

        try {
            $group = new Group($groupArray);
            $this->addGroup($group);
        } catch (\Exception $e) {
            echo "Error group ({$e->getMessage()})";
            //die();
        }
    }

    private function generateGroup($instance, Group $group)
    {
        return $this->app->group($group->getPattern(), function () use ($instance, $group) {
                $instance->loadFromArray($group->getRoutes());
        });
    }

    private function addGroup(Group $group)
    {
        if ($group->validate()) {
            $groupGen = $this->generateGroup($this, $group);
            $this->addMiddlewares($groupGen, $group->getMiddlewares());
        }
    }

    // load route from array
    private function loadRoute($name, $routearray)
    {
        // create a group
        if (false !== strpos($name, 'group')) {
            $this->loadGroup($name, $routearray);
        } else {
            $routearray['name'] = $name;
            try {
                $route = new Route($routearray);
                $this->addRoute($route);
            } catch (\Exception $e) {
                echo "Error route ({$e->getMessage()})";
                //die();
            }
        }
    }

    private function generateRoute(Route $route)
    {
        $classPrefix  = $this->params["class_prefix"];
        $methodSuffix = $this->params['method_suffix'];
        // Add route in Slim
        $action = $classPrefix.$route->getController().":".$route->getAction().$methodSuffix;
        return $this->app->map($route->getMethode(), $route->getPattern(), $action)
                ->setName($route->getName());
    }

    private function addRoute(Route $route)
    {
        if ($route->validate()) {
            $routeGen = $this->generateRoute($route);
            $this->addMiddlewares($routeGen, $route->getMiddlewares());
        }
    }

    private function addMiddlewares($route, $middlewares)
    {
        if (is_array($middlewares) && !empty($middlewares)) {
            $c = $this->app->getContainer();

            foreach ($middlewares as $middleware) {
                $route->add($c[$middleware]);
            }
        }
    }
}
