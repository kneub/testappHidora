<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\TitreMissions as ModelTitreMissions;

/**
* Handler Form TitreMissions
*/

class TitreMissions
{
    private $mission;


    public function valid()
    {
      if($this->mission->valid()){
          $this->mission->save();
          return $this->mission->no_titre_missions;
      }
      return false;
    }

    public function getMission()
    {
      return $this->mission->toJson();
    }

    public function bind(ModelTitreMissions $mission, $data)
    {
        $this->mission = $mission;
        $this->mission->fill($data);
    }
}
