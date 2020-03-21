<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Taxon extends Model
{
    public $timestamps = false;

    public $primaryKey = 'id_taxon';

    public $incrementing = false;

    protected $table = 'taxon';

    public function getFolder() : String
    {        
        switch ($this->no_rang) {
            case 6:
                $dossier = 'families';
                break;
            case 9:
                $dossier = 'genus';
                break;
            default:
                $dossier = 'species';
                break;
        }
        return $dossier;
    }

    public function getTags() : Array
    {
        $tags = [];
        
        $tags[] = $this->uid;

        if($this->nomExplore->idtaxonfamily){
            $tags[] =  $this->nomExplore->taxonFamily->uid;
        }
        if($this->nomExplore->idtaxongenus){
            $tags[] =  $this->nomExplore->taxonGenus->uid;
        }
        if($this->nomExplore->idtaxonspecies){
            $tags[] =  $this->nomExplore->taxonSpecies->uid;
        }
        if($this->nomExplore->idtaxoninfraspecies){
            $tags[] =  $this->nomExplore->taxonInfraSpecies->uid;
        }

        return $tags;
    }

    /**
     * Relationships
     */

    public function nomExplore()
    {
        return $this->hasOne('Kneub\Model\NomExplore', 'id_taxon', 'id_taxon');
    }

    public function nom()
    {
        return $this->hasOne('Kneub\Model\Nom', 'id_name', 'id_name')->select('id_name','nom_standard', 'no_rang');
    }
    public function cles()
    {
        return $this->hasMany('Kneub\Model\Cle', 'fk_id_taxon', 'id_taxon')->select('fk_id_taxon', 'id_cle', 'balise', 'descr', 'idparent');
    }
    public function clesCount()
    {
        return $this->hasMany('Kneub\Model\Cle', 'fk_id_taxon', 'id_taxon')->selectRaw('fk_id_taxon, count(*) as count')->groupBy('fk_id_taxon');
    }

    public function descriptions()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_taxon', 'id_taxon');
    }
    public function descriptionsCount()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_taxon', 'id_taxon')->selectRaw('fk_id_taxon, count(*) as count')->groupBy('fk_id_taxon');
    }
}
