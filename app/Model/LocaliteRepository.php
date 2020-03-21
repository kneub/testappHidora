<?php

namespace Kneub\Model;

use Kneub\Model\Localite;

class LocaliteRepository extends Localite
{
    /**
     * Localite by id_loc
     * @return object  Localite
     */
    public function getById($id, $relationships = '')
    {
        $dep = [];
        if(!empty($relationships)){
            $dep = explode(",", $relationships);
        }

        $dep['utilisateur'] =  function($query){
            $query->select('id','login');
        };

        return Localite::where('id_loc', $id)
            ->with($dep)->first();
    }

    /**
     * All Localite ordered by id_loc
     * @return collection of Localite
     */
    public function getAll()
    {
        return Localite::take(200)->skip(0)->orderBy('full_name', 'asc')->get();
    }

    /**
     * Get last [$nb] Localite ordered by id_loc
     * @return Collection of Localite
     */
    public function getLast($nb)
    {
        return Localite::take($nb)->skip(0)->orderBy('id_loc', 'desc')->get();
    }

    /**
     * Get Localites by full_name
     * @param  String $search term of research
     * @return Collection of localites
     */
    public function getByFullName($search, int $page = 1, $pagination = 10)
    {
        if(empty($search)){
          $count = Localite::count();
          $offset = ($page-1) * $pagination;
          $localites = Localite::skip($offset)->take($pagination)->orderBy('full_name', 'asc')->get();

          return ['list' => $localites, 'nbPages' => ceil($count / $pagination),'nbTotal' => $count, 'itemsByPage' => $pagination];
        }

        $count = Localite::where('full_name', 'ilike', "%{$search}%")->count();
        $offset = ($page-1) * $pagination;
        $localites = Localite::where('full_name', 'ilike', "%{$search}%")->skip($offset)->take($pagination)->orderBy('full_name', 'asc')->get();

        return ['list' => $localites, 'nbPages' => ceil($count / $pagination),'nbTotal' => $count, 'itemsByPage' => $pagination];
    }

    public function getByPays($nom)
    {
    	return Localite::where('fk_pays', $nom)->orderBy('full_name', 'asc')->get();
    }

    public function getByPaysAndSearch($nom, $critere)
    {
        return Localite::where('fk_pays', $nom)->where('full_name', 'ilike', "%{$critere}%")->orderBy('full_name', 'asc')->get();
    }

    public function getByDistance(int $id, int $distance){
        
        $loc = $this->getById($id);
        return Localite::with('recoltes', 'recoltes.collecteur', 'recoltes.localite', 'recoltes.nom')->whereRaw('distance_between_points(:longitude, :latitude, long_dec, lat_dec) < :distance AND id_loc <> :id', ["longitude"=> $loc->long_dec, "latitude"=> $loc->lat_dec, "distance" => $distance, "id" => $loc->id_loc])->get();
    }

    public function getCoordsByCollecteurIdOld(int $id)
    {
        return Localite::select('id_loc', 'lat_dec', 'long_dec', 'fk_pays')
                        ->whereHas('recoltes', function ($query) use ($id) {
                            $query->where('fk_id_collecteur', $id);
                        })->orderBy('fk_pays')->get();
    }

    public function getDuplications()
    {
        return Localite::select('lat_dec', 'long_dec')->groupBy('lat_dec','long_dec')->havingRaw('count(*) >  1')->get(); 
    }

    public function getByCoords($lat, $lng)
    {
        return Localite::select('id_loc', 'full_name')->whereRaw('lat_dec::text = :lat AND long_dec::text = :lng', ['lat' => $lat, 'lng' => $lng])->get(); 
    }
}
