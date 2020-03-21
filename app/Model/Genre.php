<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    public $timestamps = false;

    protected $table = 'genre';

    protected $guarded = ['id_name'];

    /**
     * Relationships
     *
     */
    public function famille()
    {
        return $this->belongsTo('Kneub\Model\Famille', 'fk_id_famille', 'id_name');
    }

    public function especes()
    {
        return $this->hasMany('Kneub\Model\Espece', 'fk_id_genre', 'id_name')
                    ->where('no_rang','>=', 15)
                    ->orderBy('nom_standard');
    }

    public function recoltes()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'id_name', 'id_name');
    }

    public function recoltesCount()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'id_name', 'id_name')->selectRaw('id_name, count(*) as count')->groupBy('id_name');
    }

    public function clesCount()
    {
        return $this->hasMany('Kneub\Model\Cle', 'fk_id_name', 'id_name')->selectRaw('fk_id_name, count(*) as count')->groupBy('fk_id_name');
    }
    public function cles()
    {
        return $this->hasMany('Kneub\Model\Cle', 'fk_id_name', 'id_name')->select('id_cle', 'fk_id_name')->orderBy('idparent', 'asc')->orderby('gauche', 'asc');
    }

    public function descriptions_tempCount()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_name_ori', 'id_name')->selectRaw('fk_id_name_ori, count(*) as count')->groupBy('fk_id_name_ori');
    }

    public function taxon()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_name', 'id_name');
    }

    /*
    public function descriptions()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_name', 'id_name')->orderBy('id_description', 'asc');
    }

    public function descriptionsCount()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_name', 'id_name')->selectRaw('fk_id_name, count(*) as count')->groupBy('fk_id_name');
    }


    */
}
