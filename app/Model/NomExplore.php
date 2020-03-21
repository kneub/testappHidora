<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class NomExplore extends Model
{
    public $timestamps = false;

    protected $table = 'nom_v_node_explore';

    protected $guarded = ['id_name'];


    /**
     * Relationships
     *
     */

    public function taxon()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_name', 'id_name');
    }

    public function taxonFamily()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_taxon', 'idtaxonfamily');
    }

    public function taxonGenus()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_taxon', 'idtaxongenus');
    }

    public function taxonSpecies()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_taxon', 'idtaxonspecies');
    }

    public function taxonInfraSpecies()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_taxon', 'idtaxoninfraspecies');
    }

}
