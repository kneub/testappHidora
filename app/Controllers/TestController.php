<?php

namespace Kneub\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Kneub\Model\UserRepository;

class TestController extends Controller
{
    private $tabNbFirst = [];

    public function testAction(RequestInterface $request, ResponseInterface $response)
    {
        $listSexy = $this->getListNbFirstSexy(1000000);
        echo implode("<br>",$listSexy);
    }

    private function nb_first(int $limit) : array {
        // init
        for ($i=2; $i <= $limit; $i++) { 
            $tb[$i] = true;
        }
        // switch to false for all multiple
        for ($j=2; $j <= intval(count($tb)/2)-1; $j++) {
            for ($k=$j+$j; $k <= count($tb)-1; $k = $k + $j) { 
                $tb[$k] = false;
            }
        }
        // remove all false value and get all keys
        return array_keys(array_filter($tb, function( $nb ){ return $nb !==  false; }));

    }

    private function check_nb_first($nb){
        if(in_array($nb, $this->tabNbFirst)){
            return true;
        }
        return false;
    }

    private function check_nb_first_sexy($nb){
        if($this->check_nb_first($nb) && $this->check_nb_first($nb + 6)) {
            return "($nb, ".($nb + 6).")";
        }
        return false;
    }

    private function getListNbFirstSexy($limit){
        $this->tabNbFirst = $this->nb_first($limit);
        $list = [];
        foreach ($this->tabNbFirst as $nb) {
            if($couple = $this->check_nb_first_sexy($nb)) {
                $list[] = $couple;
            }
        }
        return $list;
    }
}
