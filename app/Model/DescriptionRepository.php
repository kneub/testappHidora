<?php

namespace Kneub\Model;

use Kneub\Model\Description;

class DescriptionRepository extends Description
{
    /**
     * @param  int id_description
     * @return Collection of Description
     */
    public function getById($id)
    {
        return Description::find($id);
    }

    /**
     * @param int  $idName
     * @return Collection of Description
     */
    public function getByIdTaxon(int $idTaxon)
    {
        return Description::where('fk_id_taxon', $idTaxon)->with('nom_ori')->orderBy('id_description', 'asc')->get();
    }
}
