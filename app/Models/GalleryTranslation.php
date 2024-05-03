<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GalleryTranslation extends Model
{
    use SoftDeletes;
    use CrudTrait;

    protected $dates = ['deleted_at'];
 
    protected $fillable = [
        'gallery_id',
        'locale',
        'description',
        'slug'
    ];

    public function galleries()
    {
        return $this->belongsTo(Gallery::class);
    }
}
