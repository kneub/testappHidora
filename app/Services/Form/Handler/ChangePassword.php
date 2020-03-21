<?php

namespace Kneub\Services\Form\Handler;

use Kneub\Model\User;
use Kneub\Services\Validator\ValidatorInterface;

/**
* Handler Form ChangePassword
*/

class ChangePassword
{
    private $user;
    //private $oldPwd;
    //private $newPwd1;
    //private $newPwd2;

    public function __construct(ValidatorInterface $validator)
    {
      $this->validator = $validator;
    }

    public function updatePwdWithOldPassword($oldPassword, $newPassword, $confirmationPassword)
    {
      if ($this->user->passwordValid($oldPassword)) {
          return $this->updatePwd($newPassword, $confirmationPassword);
      } else {
        return ['notification' => ['code' => 403, 'type' => 'error', 'title' => 'Mot de passe', 'msg' => "L'ancien mot de passe ne correspond pas."]];
      }

    }

    public function updatePwd($newPassword, $confirmationPassword)
    {

        //if ($this->user->passwordValid($this->oldPwd)) {

            // new pwds are ok
            if ($newPassword === $confirmationPassword) {


                $this->validator->setRules($this->user->getRule('pwd'));

                if(false === $this->validator->validate(['pwd' => $newPassword])){
                  return ['notification' => ['code' => 403, 'type' => 'error', 'title' => 'Mot de passe', 'msg' => "Le mot de passe doit contenir au moins 8 caractères dont un chiffre, un nombre et un charactère spécial [*@#&%()]"]];
                }
                $this->user->pwd = password_hash($newPassword, PASSWORD_DEFAULT);
                if($this->user->save()){
                    return ['notification' => ['code' => 200, 'type' => 'success', 'title' => 'Mot de passe', 'msg' => "Bravo mot de passe modifié avec succès! Veuillez vous reconnecter"]];
                }
                return ['notification' => ['code' => 403, 'type' => 'error', 'title' => 'Mot de passe', 'msg' => "Une erreur est survenue lors de la modification du mot de passe."]];
            } else {
                return ['notification' => ['code' => 403, 'type' => 'error', 'title' => 'Mot de passe', 'msg' => "Le nouveau mot de passe et la confirmation ne sont pas identique."]];


            }
        //} else {
        //  return ['notification' => ['code' => 403, 'type' => 'error', 'title' => 'Mot de passe', 'msg' => "L'ancien mot de passe ne correspond pas."]];
        //}

    }

    public function bind(User $user) // string $oldPwd, string $newPwd1, string $newPwd2
    {
        $this->user = $user;
        //$this->oldPwd = $oldPwd;
        //$this->newPwd1 = $newPwd1;
        //$this->newPwd2 = $newPwd2;
    }
}
