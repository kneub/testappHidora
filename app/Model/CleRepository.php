<?php

namespace Kneub\Model;

use Kneub\Model\Cle;
use Illuminate\Database\Capsule\Manager as DB;

class CleRepository extends Cle
{
    public function getById(int $id)
    {
        return Cle::where('id_cle', $id)
            ->with('nom', 'nom_ori')
            ->select('idparent', 'gauche', 'droite', 'fk_id_name', 'id_cle', 'descr', 'id_name_ori', 'balise')
            ->first();
    }

    public function getNodesById(int $id)
    {
        return Cle::with('nom', 'nom_ori')
            ->selectRaw(" node.idparent, node.descr, node.fk_id_taxon, node.id_cle, node.fk_id_name, node.id_name_ori, node.balise")
            ->fromRaw("cle as node, cle as parent")
            ->where("parent.id_cle", (int)$id)
            ->whereRaw('node.gauche >= parent.gauche')
            ->whereRaw('node.gauche <= parent.droite')
            ->orderby('node.gauche', 'asc')
            ->get();
    }

    public function getParentsById(int $id)
    {
        return Cle::with('nom',  'nom_ori')
            ->selectRaw("parent.idparent, parent.descr, parent.fk_id_taxon, parent.id_cle, parent.fk_id_name, parent.id_name_ori, parent.balise")
            ->fromRaw("cle as node, cle as parent")
            ->where("node.id_cle", (int)$id)
            ->whereRaw('node.gauche >= parent.gauche')
            ->whereRaw('node.gauche <= parent.droite')
            ->orderby('parent.gauche', 'asc')
            ->get();
    }

    public function addNode($nodeIdParent, $cle)
    {
      return DB::select('SELECT createNodeTree(:parentId, :fkIdName, :idNameOri, :description)', ['parentId' => $nodeIdParent, 'fkIdName' => $cle['fk_id_name'], 'idNameOri' => $cle['id_name_ori'], 'description' => $cle['descr']]);
    }

    public function removeNode(int $idcle)
    {
      DB::select('SELECT deleteNodeTree(:idcle)', ['idcle' => $idcle]);
      return 1;
    }

    public function moveNode($nodeId, $nodeDestId)
    {
        // remove all nodes
        DB::select('SELECT moveNodeTree(:node, :parent)', ['node' => $nodeId, 'parent' => $nodeDestId]);
        return 1;
    }
}
