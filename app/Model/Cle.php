<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Cle extends Model
{
    public $timestamps = false;

    protected $table = 'cle';

    public $primaryKey = 'id_cle';

    protected $guarded = ['id_cle'];

    public function valid()
    {
        return true;
    }

    /*public function taxon()
    {
        return $this->belongsTo('Kneub\Model\Taxon', 'fk_id_taxon', 'id_taxon');
    }
    */
    public function nom()
    {
        return $this->hasOne('Kneub\Model\Nom', 'id_name', 'fk_id_name')->select('id_name','nom_standard', 'no_rang');
    }

    public function nom_ori()
    {
        return $this->hasOne('Kneub\Model\Nom', 'id_name', 'id_name_ori')->select('id_name','nom_standard', 'no_rang');
    }
}
