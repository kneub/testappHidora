<?php

namespace Kneub\Model;

use Kneub\Model\Pays;

class PaysRepository extends Pays
{
    /**
     * All Pays ordered by nom
     * @return collection of Pays
     */
    public function getAll()
    {
        return Pays::orderBy('nom', 'asc')->get();
    }

    public function getByNom($nom)
    {
        return Pays::where('nom', $nom)->with('localites')->with('regions')->get();
    }

    public function getBySearch($search)
    {
        return Pays::with(['recoltes' => function ($query) use ($search) {
                  $query->where('nom.genre', 'ilike', "%{$search}%");


        }])->toSql();;
    }


}
