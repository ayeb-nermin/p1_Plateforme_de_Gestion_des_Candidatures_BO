<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    use CrudTrait;

    protected $table = 'faq_categories';

    protected $guarded = ['id'];
   
    
    protected $cascadeDeletes = ['translations'];


    public $translatedAttributes = [
        'slug',
        'name',
        'description',
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'is_active',
    ];

    public function translation()
    {
        return $this->hasOne(FaqCategoryTranslations::class, 'faq_category_id')->where('locale', locale());
    }
    
    public function translations()
    {
        return $this->hasMany(FaqCategoryTranslations::class, 'faq_category_id');
    }

    // hasMany Relationships

    public function faq_category_translations()
    {
        return $this->hasMany(FaqCategoryTranslations::class, 'faq_category_id');
    }

    public function faqs()
    {
        return $this->belongsToMany(Faq::class,'categories_faqs','faq_id','faq_category_id');
    }
}
