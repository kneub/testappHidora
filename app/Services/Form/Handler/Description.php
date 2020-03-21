<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\Description as ModelDescription;

/**
* Handler Form Description
*/

class Description
{
    private $description;


    public function valid()
    {
      if($this->description->valid()){
          $this->description->save();
          return true;
      }
      return false;
    }

    public function getDescription()
    {
      return $this->description->toJson();
    }

    public function bind(ModelDescription $description, $data)
    {
        $this->description = $description;
        $this->description->fill($data);
    }
}
