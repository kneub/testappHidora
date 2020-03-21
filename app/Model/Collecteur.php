<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Collecteur extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'id_coll';

    protected $table = 'collecteur';

    protected $guarded = ['id_coll'];

    public function valid()
    {
        return true;
    }
}
