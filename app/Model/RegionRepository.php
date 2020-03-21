<?php

namespace Kneub\Model;

use Kneub\Model\Region;

class RegionRepository extends Region
{
    /**
     * All Region ordered by no_eco
     * @return collection of Region
     */
    public function getAll()
    {
        return Region::orderBy('no_eco', 'asc')->get();
    }

}
