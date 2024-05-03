<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use CrudTrait;


    protected $table = 'news_categories';
   
    
    protected $cascadeDeletes = ['translations'];


    public $translatedAttributes = [
        'slug',
        'name',
        'description',
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'is_active',
        'order',
    ];

    public function translation()
    {
        return $this->hasOne(NewsCategoryTranslation::class, 'news_category_id')->where('locale', locale());
    }
    
    public function translations()
    {
        return $this->hasMany(NewsCategoryTranslation::class, 'news_category_id');
    }

    // hasMany Relationships

    public function news_category_translations()
    {
        return $this->hasMany(NewsCategoryTranslation::class, 'news_category_id');
    }

    public function news()
    {
        return $this->belongsToMany(News::class,'categories_news','news_id','news_category_id');
    }
}
