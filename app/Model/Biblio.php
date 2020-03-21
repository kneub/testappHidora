<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Biblio extends Model
{
    public $timestamps = false;

    protected $table = 'biblio';

    protected $guarded = ['no_bib'];
}
