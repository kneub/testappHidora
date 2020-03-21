<?php

namespace Kneub\Services\Validator;

interface ValidatorInterface
{
  public function setRules(Array $values);
  public function validate(Array $fields);
}
