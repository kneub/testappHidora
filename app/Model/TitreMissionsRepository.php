<?php

namespace Kneub\Model;

use Kneub\Model\TitreMissions;

class TitreMissionsRepository extends TitreMissions
{
    /**
     * All TitreMission ordered by no_titre_missions
     * @return Collection of TitreMission
     */
    public function getAll()
    {
        return TitreMissions::orderBy('titre', 'asc')->get();
    }

    /**
     * @param  int no_titre_missions
     * @return Collection of TitreMission
     */
    public function getById($id)
    {
        return TitreMissions::where('no_titre_missions', $id)->with('recoltes', 'recoltes.localite', 'recoltes.collecteur', 'recoltes.nom')->first();
    }


    public function getBySearch($search, int $page = 1, $pagination = 10)
    {

        // without search
        if(empty($search)){
          $count = TitreMissions::count();
          $offset = ($page-1) * $pagination;

          $missions = TitreMissions::skip($offset)->take($pagination)->orderBy('titre', 'asc')->get();
          return ['list' => $missions, 'nbPages' => ceil($count / $pagination), 'nbTotal' => $count, 'itemsByPage' => $pagination];
        }

        // search by titre
        $count = TitreMissions::where('titre','ilike',"%$search%")->count();
        $offset = ($page-1)*$pagination;

        $missions = TitreMissions::where('titre','ilike',"%$search%")
            ->skip($offset)->take($pagination)->orderBy('titre', 'asc')->get();
        return ['list' => $missions, 'nbPages' => ceil($count/$pagination), 'nbTotal' => $count, 'itemsByPage' => $pagination];
    }
}
