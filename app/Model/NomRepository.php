<?php

namespace Kneub\Model;

use Kneub\Model\Nom;

class NomRepository extends Nom
{
    public function getAll()
    {
        return Nom::orderBy('nom_standard', 'asc')->get();
    }

    /**
     * @param  int    $id_name
     * @return object
     */
    public function getById($id_name)
    {
        return Nom::where('id_name', $id_name)->with('descriptions')->first();
    }

    public function getByNomStandard($critere, $limit = 0)
    {
        $res = Nom::where('nom_standard', 'ilike', '%'.$critere.'%')
                    ->where('no_rang', '>=', 6)
                    ->where('fk_id_a', '!=', 187045);  // exlu sp. pl.;

        if($limit > 0){
            $res = $res->take($limit);
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
