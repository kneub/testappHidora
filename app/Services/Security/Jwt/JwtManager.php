<?php
namespace Kneub\Services\Security\Jwt;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

class JwtManager implements JwtManagerInterface
{
    private $jwt = null;
    private $decoded = null;
    private $claims = [];
    private $secret = '';
    private $expire = 0;

    public function __construct($params)
    {
        $this->setSecret($params['secret_api']);
    }

    public function getToken(){
        return $this->jwt;
    }

    public function setToken($jwt){
        $this->jwt = $jwt;
        return $this;
    }

    public function setClaims($claims){
        $this->claims = $claims;
        return $this;
    }

    public function addClaim($key, $value){
        $this->claims[$key] = $value;
        return $this;
    }

    public function getClaims(){
        return $this->decoded->getClaims();
    }

    public function getClaim($key){
        if(!empty($this->decoded->getClaims()[$key])){
            return $this->decoded->getClaims()[$key];
        }
        return null;
    }
    // private
    public function getSecret(){
        return $this->secret;
    }

    public function setSecret($secret){
        if(!empty($secret)){
            $this->secret = $secret;
        }
        return $this;
    }

    public function getExpire(){
        return $this->expire;
    }

    public function setExpire($expire){
        if(!empty($expire)){
            $this->expire = $expire;
        }
        return $this;
    }

    public function encode(){
        $token  = (new Builder())
                ->setIssuedAt(time());

        // Add Claims
        if(!empty($this->claims) && is_array($this->claims)){
            foreach ($this->claims as $key => $value){
                $token->set($key, $value);
            }
        }
        // create signature
        $token->sign(new Sha256(), $this->getSecret());
        // Retrieves the generated token
        $this->jwt = $token->getToken();
        return $this;
    }

    public function decode(){
        $this->decoded = (new Parser())->parse((string) $this->jwt);
        $this->setClaims($this->getClaims());
        return $this;
    }

    public function verify(){
        if(null !== $this->decoded && $this->decoded->verify(new Sha256(), $this->getSecret())){
            return true;
        }
        return false;
    }

    public function validate(){
        if(null !== $this->decoded && $this->decoded->validate(new ValidationData())){
            return true;
        }
        return false;
    }
}