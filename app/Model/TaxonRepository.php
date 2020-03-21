<?php

namespace Kneub\Model;

use Kneub\Model\Taxon;

class TaxonRepository extends Taxon
{

    public function getAll()
    {
        return Taxon::get();
    }

    public function getByNomStandard($critere, $limit = 0)
    {
        $res = Taxon::where('nom_standard', 'ilike', '%'.$critere.'%');

        if($limit > 0){
            $res = $res->take($limit);
        }

        return $res->orderBy('nom_standard', 'asc')->get();
    }

    public function getById(int $id)
    {
        return Taxon::where('id_taxon', $id)->with('nom', 'nomExplore')->first();
    }
    
    public function getByIdWithDescriptions(int $id)
    {
        return Taxon::where('id_taxon', $id)->with('descriptions', 'descriptions.nom_ori')->first();
    }
}
