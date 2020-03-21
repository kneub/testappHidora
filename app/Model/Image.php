<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'id_image';

    protected $table = 'image';

    protected $guarded = ['id_image'];

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
