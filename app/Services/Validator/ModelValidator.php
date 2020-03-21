<?php
namespace Kneub\Services\Validator;


class ModelValidator extends \GUMP
{
  public function __construct($lang = 'fr')
  {
      parent::__construct($lang);
  }

  public function validateModel($model)
  {
      return $this->validate($model->toArray(), $model->getRules());
  }

  public function validate_password($field, $input, $param = NULL)
  {
      return false;
  }

  public function checkPassword()
  {
    $errors = [];

    if (strlen($this->attributes['pwd']) < 8) {
      $errors[] = "Password too short!";
    }

    if (!preg_match("#[0-9]+#", $this->attributes['pwd'])) {
      $errors[] = "Password must include at least one number!";
    }

    if (!preg_match("#[a-zA-Z]+#", $this->attributes['pwd'])) {
      $errors[] = "Password must include at least one letter!";
    }

    if (!preg_match("#[a-zA-Z]+#", $this->attributes['pwd'])) {
      $errors[] = "Password must include at least one letter!";
    }
    if ( !preg_match("#[@#/_'+-*%()=?]+#", $this->attributes['pwd'])) {
      $errors[] = "Password must include at least one special symbol!";
    }
    if (!empty($errors)) {
        return $errors;
    }
    return true;
  }

}
