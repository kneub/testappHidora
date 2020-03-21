<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\User as ModelUser;
use Kneub\Model\UserRepository;

/**
* Handler Form Connexion
*/

class Connexion
{
    private $user;
    private $numError = '';

    public function __construct()
    {
        $this->user = new ModelUser();
    }

    public function valid()
    {
        $repoUser = new UserRepository();
        $userDB = $repoUser->getByLogin($this->user->login);

        if (null !== $userDB && $userDB->passwordValid($this->user->pwd)) {
            if($userDB->tentative <= 5){
              $userDB->generateSecret();
              $userDB->tentative = 0;
              $userDB->save();
              return $userDB;
            }
        }
        return null;
    }

    public function getNumError()
    {
      return $this->numError;
    }

    public function getInfosUser()
    {
      if($this->user){
        return ['login' => $this->user->login, 'role' => $this->user->role];
      }
      return null;
    }

    public function bind(ModelUser $user)
    {
        $this->user = $user;
    }
}
