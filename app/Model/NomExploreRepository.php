<?php

namespace Kneub\Model;

use Kneub\Model\NomExplore;

class NomExploreRepository extends Nom
{
    public function getAll()
    {
        return NomExplore::orderBy('family', 'asc')
                    ->orderBy('genus', 'asc')
                    ->orderBy('species', 'asc')
                    ->orderBy('infraspecies', 'asc')
                    ->get();
    }

    /**
     * @param  int    $id_name
     * @return object
     */
    public function getByIdName($id_name)
    {
        return NomExplore::where('id_name', $id_name)->with('taxon')->first();
    }

    public function getByNomStandard($critere, $limit = 0)
    {
        $res = Nom::where('nom_standard', 'ilike', '%'.$critere.'%')
                    ->where('no_rang', '>=', 6)
                    ->where('fk_id_a', '!=', 187045);  // exlu sp. pl.;

        if($limit > 0){
            $res = $res->take($nb);
        }

        return $res->orderBy('nom_standard', 'asc')->get();
    }

    public function getListNames($name)
    {
        return Nom::select('nom_standard')
                    ->where('nom_standard', 'ilike', '%'.$name.'%')
                    ->where('fk_id_a', '>', 1)
                    ->orderBy('nom_standard','asc')
                    ->get();
    }

}
