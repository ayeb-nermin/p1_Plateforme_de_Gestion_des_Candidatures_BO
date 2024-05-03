<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class FaqCategoryTranslations extends Model
{
    use CrudTrait;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'faq_category_id',
        'locale',
        'slug',
        'name',
        'description',
    ];

    // belongsTo Relationships

    public function faq_category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }
}
