<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Famille extends Model
{
    public $timestamps = false;

    public $primaryKey = 'id_name';

    public $incrementing = false;

    protected $table = 'famille';

    /**
     * Relationships
     */

    /*
    public function descriptions()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_name', 'id_name')->orderBy('id_description', 'asc');
    }
    */
    public function taxon()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_name', 'id_name');
    }



    public function cles()
    {
        return $this->hasMany('Kneub\Model\Cle', 'fk_id_name', 'id_name')->select('id_cle', 'fk_id_name')->orderBy('idparent', 'desc')->orderby('gauche', 'asc');
    }

    public function descriptions_tempCount()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_name_ori', 'id_name')->selectRaw('fk_id_name_ori, count(*) as count')->groupBy('fk_id_name_ori');
    }

    public function genres()
    {
        return $this->hasMany('Kneub\Model\Genre', 'fk_id_famille', 'id_name')->orderBy('nom_standard');
    }

}
