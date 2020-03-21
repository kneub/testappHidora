<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Espece extends Model
{

    public $timestamps = false;

    protected $table = 'espece';

    protected $guarded = ['id_name'];

    /**
     * Relationships
     */
    public function nomAccepte()
    {
         return $this->belongsTo('Kneub\Model\Espece', 'fk_id_a', 'id_name');
    }
    public function genre()
    {
        return $this->belongsTo('Kneub\Model\Genre', 'fk_id_genre', 'id_name');
    }
    public function taxon()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_name', 'id_name');
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

    public function recoltes()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'id_a', 'fk_id_a')->orderBy('fk_pays', 'asc')->orderBy('aaaa', 'asc');
    }

    public function recoltes2()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'id_a', 'id_name');
    }

    public function recoltesCount()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'id_a', 'fk_id_a')->selectRaw('id_a, count(*) as count')->groupBy('id_a');
    }

    public function sousespeces()
    {
        return $this->hasMany('Kneub\Model\Espece', 'fk_id_parent', 'id_name');
    }

    public function observations()
    {
        return $this->belongsToMany('Kneub\Model\Observation', 'obs_name', 'fk_id_name', 'id_obs', 'id_name', 'id_obs');
    }
}
