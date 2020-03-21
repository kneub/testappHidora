<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Recolte extends Model
{
    const FOLDER = 'specimens';

    public $timestamps = false;

    protected $primaryKey = 'id_recolte';

    protected $table = 'recolte';

    protected $guarded = ['id_recolte'];

    public function valid()
    {
      return true;
    }

    public function getFolder() : String
    {        
        return self::FOLDER;
    }

    public function getTags() : Array
    {
       $tags = [];

        if(!empty($this->nom->taxon)){
            $tags = $this->nom->taxon->getTags();
        }

        $tags[] = $this->uid;

        return $tags;
    }


    /**
     * Relationships
     *
     */

    public function utilisateur()
    {
        return $this->belongsTo('Kneub\Model\User', 'fk_id_utilisateur', 'id');
    }

    public function collecteur()
    {
        return $this->belongsTo('Kneub\Model\Collecteur', 'fk_id_collecteur', 'id_coll');
    }

    public function nom()
    {
        return $this->belongsTo('Kneub\Model\Nom', 'id_name', 'id_name');
    }

    public function espece()
    {
        return $this->belongsTo('Kneub\Model\Espece', 'id_a', 'id_name');
    }

    public function genre()
    {
        return $this->belongsTo('Kneub\Model\Genre', 'id_name', 'id_name');
    }

    public function nom_accepte()
    {
        return $this->belongsTo('Kneub\Model\Nom', 'id_a', 'id_name');
    }

    public function parent()
    {
        return $this->belongsTo('Kneub\Model\Nom', 'fk_id_a', 'id_a');
    }

    public function titre_missions()
    {
        return $this->belongsTo('Kneub\Model\TitreMissions', 'fk_titre_mission', 'no_titre_missions');
    }

    public function pays()
    {
        return $this->belongsTo('Kneub\Model\Pays', 'fk_pays', 'nom');
    }

    public function localite()
    {
        return $this->belongsTo('Kneub\Model\Localite', 'fk_id_loc', 'id_loc')->orderBy('fk_pays', 'asc');
    }

    public function images()
    {
        return $this->hasMany('Kneub\Model\Image', 'fk_id_recolte', 'id_recolte')
                ->with(['nom' => function($query){
                    $query->select('id_name', 'nom_standard');
                }]);
    }
}
