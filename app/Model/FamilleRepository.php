<?php

namespace Kneub\Model;

use Kneub\Model\Famille;
use BlueM\Tree;

class FamilleRepository extends Famille
{
   
    /**
     * Get Famille by id
     * @param  int    $id_name
     * @return object
     */
    public function getById($id_name)
    {
        return Famille::where('id_name', $id_name)->with(['taxon', 'descriptions_tempCount', 'cles', 'genres','genres.especes', 'genres.especes.taxon', 'genres.clesCount', 'genres.taxon.descriptionsCount'])->first();
    }

    public function getList($name)
    {
        return Famille::select('famille')->where('famille', 'ilike', '%'.$name.'%')->orderBy('famille', 'asc')->get();
    }

    /**
     * Get Famille by name
     * @param $name
     * @param int $page
     * @param int $pagination
     * @return array
     */
    public function getByName($name, int $page = 1, $pagination = 10)
    {
        $offset = ($page-1) * $pagination;

        $familles = Famille::where('famille', $name);

        $count = $familles->count();
        $familles = $familles->skip($offset)
                            ->take($pagination)->orderBy('famille', 'asc')
                            ->with(['genres', 'genres.taxon.descriptionsCount', 'genres.cles', 'genres.clesCount'])->get();
        return ['list' => $familles, 'count' => $count];
    }
}
