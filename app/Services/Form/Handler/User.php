<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\User as ModelUser;

/**
* Handler Form Recolte
*/

class User
{
    private $user;
    private $validator;
    private $mode;

    public function __construct($validator, string $mode)
    {
        $this->user = new ModelUser();
        $this->validator = $validator;
        $this->mode = $mode;
    }

    public function valid()
    {
       if(true === $this->validator->validateModel($this->user) ){

           if($this->mode == 'create'){
             $this->user->encryptPassword();
           }

           $this->user->generateSecret();
           $this->user->save();
           return true;
       }
      return false;
    }

    public function getErrors()
    {
      $this->validator::set_field_name("login", "Nom d'utilisateur");
      $this->validator::set_field_name("pwd", "Mot de passe");
      return $this->validator->get_readable_errors();
    }

    public function bind(ModelUser $user, $data)
    {
        if(empty($data['role'])){
            $data['role'] = 'user'; // role par defaut
        }
        $user->fill($data);
        $this->user = $user;

    }
}
