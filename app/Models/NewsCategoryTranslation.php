<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class NewsCategoryTranslation extends Model
{
    use CrudTrait;


    protected $dates = ['deleted_at'];

    protected $fillable = [
        'news_category_id',
        'locale',
        'slug',
        'name',
        'description',
    ];

    // belongsTo Relationships

    public function news_category()
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }
}
