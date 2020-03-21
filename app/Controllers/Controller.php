<?php

namespace Kneub\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Controller
{

    protected $container;

    /**
     * Controller constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function is_admin($request){
        $role = $request->getAttribute('user_role');
        if($role == 'admin'){
            return true;
        }
        return false;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getService($name)
    {
        return $this->container->get($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getRepository($name)
    {
      return $this->getService('entityManager')->getRepository($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getModel($name)
    {
        return $this->getService('entityManager')->getModel($name);
    }

    /**
     * Retrieve variable from _GET
     * @param  RequestInterface $request
     * @param  string           $name
     * @return mixed
     */
    public function varGet(RequestInterface $request, $name)
    {
      $val = $request->getQueryParams()[$name];

      if(!empty($val)){
        if(is_string($val)){
          $val = trim($val);
          $val = str_replace("\xc2\xa0", ' ', $val);
        }
        return  $val;
      }
      return null;
    }

    /**
     * Retrieve variable from _POST
     * @param  RequestInterface $request
     * @param  string           $name
     * @return mixed
     */
    public function varPost(RequestInterface $request, $name)
    {
      $val = $request->getParsedBody()[$name];
      if(!empty($val)){
        if(is_string($val)){
          $val = trim($val);
          $val = str_replace("\xc2\xa0", ' ', $val);
        }
        return  $val;
      }
      return null;
    }
}
