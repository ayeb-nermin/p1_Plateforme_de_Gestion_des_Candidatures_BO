<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaqTranslation extends Model
{
    use SoftDeletes;
    use CrudTrait;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'faq_id',
        'locale',
        'reponse',
        'question',
        'slug'
    ];

    public function faq()
    {
        return $this->belongsTo(Faq::class);
    }
}
