<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'id_livre_apd';

    protected $table = 'livres';

    protected $guarded = ['id_livre_apd'];

    public function valid()
    {
        return true;
    }
}
