<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Pays extends Model
{

    public $timestamps = false;

    public $primaryKey = 'nom';

    public $incrementing = false;

    protected $table = 'pays';

    /**
     * Relationships
     *
     */
     public function localites()
     {
        return $this->hasMany('Kneub\Model\Localite', 'fk_pays', 'nom');
     }

     public function regions()
     {
        return $this->hasMany('Kneub\Model\Region', 'fk_pays', 'nom');
     }

     public function recoltes()
     {
        return $this->hasManyThrough('Kneub\Model\Recolte', 'Kneub\Model\Localite', 'fk_pays', 'fk_id_loc');
     }
}
