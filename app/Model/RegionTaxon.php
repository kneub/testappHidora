<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class RegionTaxon extends Model
{
    public $timestamps = false;

    public $primaryKey = 'no_region_taxon';

    public $incrementing = false;

    protected $table = 'region_taxon';

    /**
     * Relationships
     *
     */
     public function pays()
     {
        return $this->belongsTo('Kneub\Model\Nom', 'id_name_a', 'nom');
     }

   
}
