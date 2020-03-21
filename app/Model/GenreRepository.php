<?php

namespace Kneub\Model;

use Kneub\Model\Genre;

class GenreRepository extends Genre
{
    public function getById($id_name)
    {
        return Genre::where('id_name', $id_name)->with(['famille', 'cles', 'taxon', 'taxon.descriptions', 'descriptions_tempCount', 'especes','especes.taxon', 'especes.clesCount', 'especes.taxon.descriptionsCount', 'especes.recoltesCount'])->first();
    }

    public function getList($name)
    {
        return Genre::select('nom_standard')->where('nom_standard', 'ilike', '%'.$name.'%')->orderBy('nom_standard', 'asc')->get();
    }

    public function getByName($name, int $page = 1, $pagination = 10)	
    {	
        $offset = ($page-1) * $pagination;	
         $genres = Genre::where('nom_standard', $name);	
         $count = $genres->count();	
        $genres = $genres->skip($offset)->take($pagination)->orderBy('nom_standard', 'asc')->with(['especes', 'especes.clesCount', 'especes.taxon.descriptionsCount', 'especes.recoltesCount'])->get();	
        return ['list' => $genres, 'count' => $count];	
    }

    public function getListByIds($ids)
    {
        return Genre::whereIn('id_name', $ids)->with(['recoltes', 'recoltes.localite', 'recoltes.nom'])->orderBy('nom_standard', 'asc')->get();
    }    
}
