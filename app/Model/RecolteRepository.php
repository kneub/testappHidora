<?php

namespace Kneub\Model;

use Kneub\Model\Recolte;

class RecolteRepository extends Recolte
{
    /**
     * All Recolte ordered by id_recolte
     * @return Collection of Recolte
    */
    public function getAll()
    {
        return Recolte::orderBy('id_recolte', 'asc')->get();
    }

    /**
     * Get Recolte ordered by id_recolte
     * @return Collection of Recolte
     */
    public function getList($start = 0, $nb = 10)
    {
        return Recolte::orderBy('id_recolte', 'asc')->take($nb)->skip($start)->get();
    }

    /**
     * Last nb recoltes
     * @param  int    $nb max recoltes
     * @return Collection of Recolte
    */
    public function getLast($nb = 10)
    {
        return Recolte::orderBy('id_recolte', 'desc')->take($nb)->get();
    }

    /**
     * @param  int    $id id_name
     * @return object Nom
    */

    public function getById(int $id)
    {
        return Recolte::where('id_recolte', $id)
            ->with(['utilisateur' => function($query){
                $query->select('id','login');
                //'images'
            },'localite.pays', 'localite', 'genre', 'genre.famille', 'espece', 'espece.genre','espece.genre.famille', 'titre_missions', 'collecteur', 'nom', 'nom.taxon', 'nom.taxon.nomExplore'])->first();
    }

    /**
     * @param  int    $id id_name
     * @return Collection of Nom
     */
    public function getByIds($liste)
    {
        return Recolte::whereIn('id_recolte', $liste)
            ->with(['localite.pays', 'localite', 'nom', 'nom.famille', 'titre_missions', 'collecteur'])->orderBy('id_recolte', 'asc')->get();
    }

    public function getListCodeBarre($search)
    {
        return Recolte::distinct()->select('codebarre')->where('codebarre', 'ilike', '%'.$search.'%')->orderBy('codebarre','asc')->get();
    }

    public function getByCodeBarre($search)
    {
        return Recolte::with(['localite', 'nom', 'collecteur'])->where('codebarre', 'ilike', '%'.$search.'%')->orderBy('codebarre','asc')->get();
    }

    public function getIndet(int $page = 1, int $pagination= 10, $filters = null)
    {
        $offset = ($page-1) * $pagination;

        $query = Recolte::join('collecteur', 'recolte.fk_id_collecteur', '=', 'collecteur.id_coll')
                        ->with(['localite', 'nom', 'collecteur'])
                        ->where('id_name', 1)
                        ->when($filters['collecteurFilter'], function ($query) use ($filters) {
                           return $query->where('collecteur.nom', 'ilike', '%'.$filters['collecteurFilter'].'%');
                        })
                        ->when($filters['ncollFilter'], function ($query) use ($filters) {
                            return $query->where('ncoll','ilike', '%'.$filters['ncollFilter'].'%');
                        })
                        ->orderBy('collecteur.nom', 'asc')
                        ->orderby('ncoll', 'asc');

        $count = $query->count();

        $recoltes = $query->skip($offset)->take($pagination)->get();

        return ['list' => $recoltes, 'nbPages' => ceil($count / $pagination), 'nbTotal' => $count, 'itemsByPage' => $pagination];
    }

    public function getCoordsByCollecteurId(int $id)
    {
        return Recolte::with('localite:id_loc,lat_dec,long_dec', 'collecteur', 'nom', 'localite')
            ->where('fk_id_collecteur', $id)
            ->get();
    }

    public function getCoordsByMissionId(int $id)
    {
        //'recoltes', 'recoltes.localite', 'recoltes.collecteur', 'recoltes.nom'
        return Recolte::with( 'collecteur', 'nom', 'localite')
            ->where('fk_titre_mission', $id)
            ->get();
    }

    /**
     * SCOPE
    */
     public function scopeFilterNom($query, $search)
     {
        return $query->whereHas('nom', function ($query) use ($search) {
                $query->where('genre', 'ilike', "%{$search}%");
              })->orderBy('id_recolte', 'desc');
     }

}
