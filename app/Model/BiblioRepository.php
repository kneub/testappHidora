<?php

namespace Kneub\Model;

use Kneub\Model\Biblio;

class BiblioRepository extends Biblio
{
    /**
     * All Biblio ordered by no_bib
     * @return object list of Biblio
     */
    public function getAll()
    {
        return Biblio::orderBy('no_bib', 'asc')->get();
    }

    /**
     * @param  int no_bib
     * @return object Biblio
     */
    public function getById($id)
    {
        return Biblio::find($id);
    }
}
