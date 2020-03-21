<?php

namespace Kneub\Model;

use Kneub\Model\Collecteur;

class CollecteurRepository extends Collecteur
{
    /**
     * All Collecteur ordered by nom
     * @return Collection of Collecteur
     */
    public function getAll()
    {
        return Collecteur::orderBy('nom', 'asc')->get();
    }

    /**
     * @param  int id_coll
     * @return Object Collecteur
     */
    public function getById(int $id)
    {
        return Collecteur::find($id);
    }

    /**
     * List of Collecteur with search
     * @param $name
     * @return Collection of Collecteur
     */
    public function getList($name)
    {
      return Collecteur::select('nom')->where('nom', 'ilike', '%'.$name.'%')
                        ->orderBy('nom', 'asc')
                        ->get();
    }

    /**
     * List of Collecteur with search and pagination
     * @param $search
     * @param int $page
     * @param int $pagination
     * @return array
     */
    public function getBySearch($search, int $page = 1, int $pagination = 10)
    {

        // without search
        if(empty($search)){
          $count = Collecteur::count();
          $offset = ($page-1) * $pagination;

          $collecteurs = Collecteur::skip($offset)->take($pagination)->orderBy('nom', 'asc')->get();
          return ['list' => $collecteurs, 'nbPages' => ceil($count / $pagination), 'nbTotal' => $count, 'itemsByPage' => $pagination];
        }

        // search by nom
        $count = Collecteur::where('nom','ilike',"%$search%")->count();
        $offset = ($page-1)*$pagination;

        $collecteurs = Collecteur::where('nom','ilike',"%$search%")
            ->skip($offset)->take($pagination)->orderBy('nom', 'asc')->get();
        return ['list' => $collecteurs, 'nbPages' => ceil($count/$pagination), 'nbTotal' => $count, 'itemsByPage' => $pagination];
    }
}
