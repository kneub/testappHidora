<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    public $timestamps = false;

    public $primaryKey = 'no_eco';

    public $incrementing = false;

    protected $table = 'region';

    /**
     * Relationships
     *
     */
    public function regionsTaxon()
    {
        return $this->hasMany('Kneub\Model\RegionTaxon', 'fk_no_eco', 'no_eco');
     }
    
}
