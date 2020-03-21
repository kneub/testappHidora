<?php

namespace Kneub\Model;

use Kneub\Model\Observation;

class ObservationRepository extends Observation
{
    /**
     * @param  int id_obs
     * @return Object Observation
     */
    public function getById(int $id)
    {
        return Observation::where('id_obs', $id)->with(['names', 'collecteur', 'livre', 'localite', 'localite.pays'])->first();
    }
}
