<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\Localite as ModelLocalite;

/**
* Handler Form Localite
*/

class Localite
{
    private $localite;

    public function __construct()
    {
        //$this->recolte = new ModelRecolte();
    }

    public function valid()
    {
        if($this->localite->valid()){
            $this->localite->save();
            return $this->localite->id_loc;
        }
        return false;
    }

    public function getLocalite()
    {
      return $this->localite->toJson();
    }

    public function bind(ModelLocalite $localite, $data)
    {
        $this->localite = $localite;
        $this->localite->fill($data);
    }
}
