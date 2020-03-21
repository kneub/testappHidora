<?php

namespace Kneub\Model;

use Kneub\Model\RegionTaxon;

class RegionTaxonRepository extends RegionTaxon
{
    /**
     * All Region ordered by no_region_taxon
     * @return Collection of Region
     */
    public function getAll()
    {
        return Region::orderBy('no_region_taxon', 'asc')->get();
    }

}
