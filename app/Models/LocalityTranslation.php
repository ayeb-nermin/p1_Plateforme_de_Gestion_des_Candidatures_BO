<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LocalityTranslation extends Model
{
    use SoftDeletes;
    use CrudTrait;

    protected $fillable = [
        'locality_id',
        'locale',
        'title',
    ];

    // belongsTo Relationships

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }
}
