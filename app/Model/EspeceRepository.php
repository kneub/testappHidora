<?php

namespace Kneub\Model;

use Kneub\Model\Espece;
use Kneub\Model\Genre;
use Kneub\Model\Recolte;

class EspeceRepository extends Espece
{

    /**
     * @param  int    $id_name
     * @return object
     */
    public function getById($id_name)
    {
        return Espece::where('id_name', $id_name)->with(['descriptions_tempCount', 'genre', 'genre.famille', 'taxon', 'sousespeces', 'observations', 'observations.collecteur', 'observations.livre', 'observations.localite', 'nomAccepte', 'cles', 'clesCount'])->first();
    }

    /**
     * @param $name
     * @return Collection of Espece
     */
    public function getList($name)
    {
        return Espece::select('nom_standard')->where('nom_standard', 'ilike', '%'.$name.'%')
            ->where('no_rang', '>=', 15)
            ->orderBy('nom_standard','asc')
            ->get();
    }

    /****************************************************
     * SCOPES
    *****************************************************/

    public function getSearchByType($type, $search, int $page = 1, $pagination = 10, $filters = null)
    {

      if(empty($type) || !in_array($type, ['G', 'SP', 'C', 'CB'])){
          return ['list' => [], 'count' => 0];
      }

      if(empty($search)){
        return ['list' => [], 'count' => 0];
      }

      $offset = ($page-1) * $pagination;

      // search by genre
      if($type == 'G'){
        $especes = Espece::bySearchG($search, $filters);
        // search by nom
      }elseif ($type == 'SP'){
        $especes = Espece::bySearchSP($search, $filters);
      // search by collecteur
      }elseif ($type == 'C'){
        $especes = Espece::bySearchColl($search, $filters);
      // search by codebarre
      }elseif ($type == 'CB'){
        $especes = Espece::bySearchCodebarre($search);
      }

      $count = $especes->count();

      // list count genre en list id_name (genre)
      $listNom = $especes->get();
      
      $tb = [];
      $total = 0;
      foreach ($listNom as $n) {
          if(!in_array($n['fk_id_genre'], $tb) && !empty($n->genre->recoltesCount[0]->count)){
            $tb[] = $n['fk_id_genre'];
            $total += $n->genre->recoltesCount[0]->count;
          }
      }

      $especes = $especes->skip($offset)->take($pagination)->orderBy('nom_standard', 'asc')->get();
      return ['list' => $especes, 'count' => $count, 'totalRecoltes' => $total, 'idsGenres' => $tb ];

  }

    public function scopeBySearchG($query, $search, $filters)
    {
       return $query->with(['genre' => function($query){
                    $query->select('id_name');
                }, 'genre.recoltesCount', 'recoltes' => function($query) use ($filters){
                  // filters ncoll OR aaaa
                  $query->when($filters['ncollFilter'], function ($query) use ($filters) {
                       $query->where(function($query) use ($filters){
                           $query->where('ncoll','ilike', '%'.$filters['ncollFilter'].'%')
                                 ->when($filters['aaaaFilter'], function ($query) use ($filters) {
                                   return $query->orWhere('aaaa','=', $filters['aaaaFilter']);
                                 });
                       });
                   }, function ($query) use ($filters) {
                        $query->when($filters['aaaaFilter'], function ($query) use ($filters) {
                           return $query->where('aaaa','=', $filters['aaaaFilter']);
                       });
                   })
                  ->orderBy('fk_pays', 'asc')->orderBy('aaaa', 'asc');

               }, 'recoltes.utilisateur', 'recoltes.collecteur', 'recoltes.localite', 'recoltes.nom_accepte', 'recoltes.nom'])
               ->whereIn('id_name', function($query) use ($search, $filters){
                   $query->select('id_a')
                         ->from(with(new Recolte)->getTable())
                         ->whereIn('id_a', function($query) use ($search, $filters){
                           $query->select('fk_id_a')
                                 ->from(with(new Espece)->getTable())
                                 //->where('genre', $search)
                                 ->whereIn('fk_id_genre', function($query) use ($search){
                                     $query->select('id_name')
                                           ->from(with(new Genre)->getTable())
                                           ->where('nom_standard', $search)
                                           ->get();
                                 })
                                 ->where('fk_id_a', '>', 1)  // exlu les inconnus
                                 ->where('fk_id_a', '!=', 187045)  // exlu sp. pl.
                                 //->where('no_rang','>', 9) // no_rang = 9 est pour GENRE
                                 ->get();
                        })
                        // filters ncoll OR aaaa
                        ->when($filters['ncollFilter'], function ($query) use ($filters) {
                            $query->where(function($query) use ($filters){
                                $query->where('ncoll','ilike', '%'.$filters['ncollFilter'].'%')
                                      ->when($filters['aaaaFilter'], function ($query) use ($filters) {
                                          return $query->orWhere('aaaa','=', $filters['aaaaFilter']);
                                      });
                            });
                        }, function ($query) use ($filters) {
                             $query->when($filters['aaaaFilter'], function ($query) use ($filters) {
                                return $query->where('aaaa','=', $filters['aaaaFilter']);
                            });
                        })
                        ->get();
               });
    }


   public function scopeBySearchCodebarre($query, $search)
   {
      return $query->with(['genre' => function($query){
                   $query->select('id_name');
               }, 'genre.recoltesCount', 'recoltes' => function($query){
                $query->orderBy('fk_pays', 'asc')->orderBy('aaaa', 'asc');
              }, 'recoltes.utilisateur', 'recoltes.collecteur', 'recoltes.localite', 'recoltes.nom_accepte', 'recoltes.nom'])
              ->whereIn('id_name', function($query) use ($search){
                $query->select('id_a')
                      ->from('recolte')
                      ->where('codebarre', $search)
                      ->get();
              });
   }

   public function scopeBySearchSP($query, $search, $filters)
   {
       return $query->with(['genre' => function($query){
                    $query->select('id_name');
                }, 'genre.recoltesCount', 'recoltes' => function($query) use ($filters){
               // filters ncoll OR aaaa
               $query->when($filters['ncollFilter'], function ($query) use ($filters) {
                   $query->where(function($query) use ($filters){
                       $query->where('ncoll','ilike', '%'.$filters['ncollFilter'].'%')
                           ->when($filters['aaaaFilter'], function ($query) use ($filters) {
                               return $query->orWhere('aaaa','=', $filters['aaaaFilter']);
                           });
                   });
               }, function ($query) use ($filters) {
                   $query->when($filters['aaaaFilter'], function ($query) use ($filters) {
                       return $query->where('aaaa','=', $filters['aaaaFilter']);
                   });
               })
              ->orderBy('fk_pays', 'asc')->orderBy('aaaa', 'asc');
           }, 'recoltes.utilisateur', 'recoltes.collecteur', 'recoltes.localite', 'recoltes.nom_accepte', 'recoltes.nom'])
           ->whereIn('id_name', function($query) use ($search, $filters){
               //return Recolte::select('id_a')
               $query->select('id_a')
                   ->from('recolte')
                   ->whereIn('id_a', function($query) use($search){
                     $query->select('fk_id_a')
                           ->from('nom')
                           ->where('fk_id_a', '>', 1) // exlu les inconnus
                           ->where('fk_id_a', '!=', 187045)  // exlu sp. pl.
                           ->where('nom_standard', $search)
                           ->get();
                   })

                   // filters ncoll OR aaaa
                   ->when($filters['ncollFilter'], function ($query) use ($filters) {
                        $query->where(function($query) use ($filters){
                            $query->where('ncoll','ilike', '%'.$filters['ncollFilter'].'%')
                                  ->when($filters['aaaaFilter'], function ($query) use ($filters) {
                                    return $query->orWhere('aaaa','=', $filters['aaaaFilter']);
                                  });
                        });
                    }, function ($query) use ($filters) {
                         $query->when($filters['aaaaFilter'], function ($query) use ($filters) {
                            return $query->where('aaaa','=', $filters['aaaaFilter']);
                        });
                    })
                   ->get();
           });
   }

   public function scopeBySearchColl($query, $search, $filters)
   {
     return $query->with(['genre' => function($query){
                  $query->select('id_name');
              }, 'recoltes' => function($query) use ($search, $filters) {
                     $query->wherehas('collecteur', function($query) use ($search){
                        $query->where('nom', 'ilike', "%{$search}%");
                     })
                     ->when($filters['ncollFilter'], function ($query) use ($filters) {
                          $query->where(function($query) use ($filters){
                              $query->where('ncoll','ilike', '%'.$filters['ncollFilter'].'%')
                                    ->when($filters['aaaaFilter'], function ($query) use ($filters) {
                                      return $query->orWhere('aaaa','=', $filters['aaaaFilter']);
                                    });
                          });
                      }, function ($query) use ($filters) {
                           $query->when($filters['aaaaFilter'], function ($query) use ($filters) {
                              return $query->where('aaaa','=', $filters['aaaaFilter']);
                          });
                      })
                     ->orderBy('fk_pays')
                     ->orderBy('aaaa', 'asc');
             }, 'genre.recoltesCount', 'recoltes.utilisateur', 'recoltes.localite', 'recoltes.collecteur', 'recoltes.nom_accepte', 'recoltes.nom'])
           
            ->wherehas('recoltes.collecteur', function($q) use ($search){
                $q->where('nom', 'ilike', "%{$search}%");
           })
           
           ->wherehas('recoltes2', function($query) use ($search, $filters){
                     $query->where('id_a', '>', 1) // exlu les inconnus
                     // filters ncoll OR aaaa
                     ->when($filters['ncollFilter'], function ($query) use ($filters) {
                          $query->where(function($query) use ($filters){
                              $query->where('ncoll','ilike', '%'.$filters['ncollFilter'].'%')
                                    ->when($filters['aaaaFilter'], function ($query) use ($filters) {
                                      return $query->orWhere('aaaa','=', $filters['aaaaFilter']);
                                    });
                          });
                      }, function ($query) use ($filters) {
                           $query->when($filters['aaaaFilter'], function ($query) use ($filters) {
                              return $query->where('aaaa','=', $filters['aaaaFilter']);
                          });
                      })
                      ->orderBy('fk_pays')
                      ->orderBy('aaaa', 'asc');
             });
   }
}
