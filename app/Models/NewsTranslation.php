<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewsTranslation extends Model
{
    use SoftDeletes;
    use CrudTrait;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'news_id',
        'locale',
        'slug',
        'title',
        'short_description',
        'long_description',
    ];

    // belongsTo Relationships

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
