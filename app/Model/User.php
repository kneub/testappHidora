<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class User extends Model
{
    private $validateRules = [
        'login'  => "required|alpha_numeric|max_len,100|min_len,4",
	      'pwd'    => "min_len,8 |regex, #[0-9]+# |regex, #[a-zA-Z]+# |regex, #[@/_'%()=?&\#\+\-\*]+#",
	      'role'   => "required|contains, 'admin' 'user'"
    ];

    public $timestamps = false;

    protected $table = 'utilisateur';

    protected $guarded = ['id'];

    protected $hidden = ['pwd'];


    /**
     * return validations rules
     * @return array  list of rules
     */
    public function getRules()
    {
      return $this->validateRules;
    }

    public function getRule($key)
    {
        if(!empty($this->validateRules[$key])){
          return [$key => $this->validateRules[$key]];
        }
        return false;
    }

    /**
     * check password with password's hash
     * @param  string $password
     * @return boolean
     */
    public function passwordValid($password)
    {
        return password_verify($password, $this->attributes['pwd']);
    }

    public function generateSecret()
    {
        $this->attributes['secret'] = md5(uniqid());
        return $this;
    }
    /**
     * hash paswsword
     * @return $this
     */
    public function encryptPassword()
    {
      $this->attributes['pwd'] = password_hash($this->attributes['pwd'], PASSWORD_DEFAULT);
      return $this;
    }
}
