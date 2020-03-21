<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Description extends Model
{
    public $timestamps = false;

    protected $table = 'description';

    public $primaryKey = 'id_description';

    protected $guarded = ['id_description'];

    public function valid()
    {
        return true;
    }

    public function taxon()
    {
        return $this->belongsTo('Kneub\Model\Taxon', 'fk_id_taxon', 'id_taxon');
    }

    public function nom_ori()
    {
        return $this->hasOne('Kneub\Model\Nom', 'id_name', 'fk_id_name_ori')->select('id_name','nom_standard', 'no_rang');
    }
}
