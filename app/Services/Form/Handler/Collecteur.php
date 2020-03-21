<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\Collecteur as ModelCollecteur;

/**
* Handler Form Collecteur
*/

class Collecteur
{
    private $collecteur;


    public function valid()
    {
      if($this->collecteur->valid()){
          $this->collecteur->save();
          return $this->collecteur->id_coll;
      }
      return false;
    }

    public function getCollecteur()
    {
      return $this->collecteur->toJson();
    }

    public function bind(ModelCollecteur $collecteur, $data)
    {
        $this->collecteur = $collecteur;
        $this->collecteur->fill($data);
    }
}
