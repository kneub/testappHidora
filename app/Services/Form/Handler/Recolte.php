<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\Recolte as ModelRecolte;

/**
* Handler Form Recolte
*/

class Recolte
{
    private $recolte;

    public function __construct()
    {
        //$this->recolte = new ModelRecolte();
    }

    public function valid()
    {
      if($this->recolte->valid()){
          $this->recolte->save();
          return true;
      }
      return false;
    }

    public function getRecolte()
    {
      return $this->recolte;
    }

    public function bind(ModelRecolte $recolte, $data)
    {
        $this->recolte = $recolte;

        // checkbox unchecked cf
        /*if(!array_key_exists('cf', $data)){
          $data['cf'] = NULL;
        }
        // checkbox unchecked incertain
        if(!array_key_exists('incertain', $data)){
          $data['incertain'] = '0';
        }
        // checkbox unchecked typus
        if(!array_key_exists('typus', $data)){
          $data['typus'] = '0';
        }*/

        $this->recolte->fill($data);
    }
}
