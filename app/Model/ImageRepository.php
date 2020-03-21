<?php

namespace Kneub\Model;

use Kneub\Model\Image;

class ImageRepository extends Image
{

    /**
     * @param  int id_image
     * @return Object Image
     */
    public function getById(int $id)
    {
        return Image::find($id);
    }

    /**
     * @param  int fk_id_name
     * @return Object Image
     */
    public function getByIdName(int $idname)
    {
        return Image::byId()->where('fk_id_name', $idname)->get();
    }

    public function getByIdNames($idnames)
    {
        return Image::byId()->whereIn('fk_id_name', $idnames)->get();
    }

    public function getByIdTaxon(int $id)
    {
        return Image::byId()->where('fk_id_taxon', $id)->with(['taxon.nom', 'nom_ori'])->get();
    }

    public function getByIdTaxons($ids)
    {
        return Image::byId()->whereIn('fk_id_taxon', $ids)->with(['taxon.nom'])->get();
    }

    public function getAllByTaxon()
    {
        return Image::with('taxon')
            ->selectRaw("t.id_taxon, i.dossier, i.image, n.no_rang, i.fk_id_recolte")
            ->fromRaw("taxon t, nom n, image i")
            ->whereRaw('t.id_name = n.id_name and fk_id_taxon = t.id_taxon')
            ->orderby('t.id_taxon', 'asc')
            ->skip(4000)->take(5000)
            ->get();
    }

    /** SCOPE */
    public function scopeById($query)
    {
       return $query->select('id_image', 'dossier', 'image', 'figcaption', 'auteur', 'fk_id_recolte', 'fk_id_name_ori')
               ->with(['nom_ori' => function($query){
                       $query->select('id_name', 'nom_standard');
                   }]);
    }

}
