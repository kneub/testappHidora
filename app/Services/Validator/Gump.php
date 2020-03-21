<?php

namespace Kneub\Services\Validator;

use Kneub\Services\Validator\ValidatorInterface;

class Gump implements ValidatorInterface
{
  private $validator;

  public function  __construct($validator)
  {
      $this->validator = $validator;
  }

  public function setRules(Array $values)
  {
      $this->validator->validation_rules($values);
  }

  public function validate(Array $fields)
  {
    return $this->validator->run($fields);
  }

}
