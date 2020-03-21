<?php
namespace Kneub\Services\Database;

class EntityManager
{
    private $container;
    const NSREPOSITORY  = 'Kneub\Model';
    const NSMODEL       = 'Kneub\Model';

    public function __construct($container) {
        $this->container = $container;
    }

    public function getRepository($name) {
        $repository = self::NSREPOSITORY.'\\'.ucfirst($name)."Repository";
        return new $repository();
    }

    public function getModel($name){
      $model = self::NSMODEL.'\\'.ucfirst($name);
      return new $model();
    }
}
