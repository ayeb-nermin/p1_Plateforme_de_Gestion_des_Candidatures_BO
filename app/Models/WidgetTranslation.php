<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class WidgetTranslation extends Model
{
    use CrudTrait;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'widget_id',
        'locale',
        'title',
        'description',
        'button_title',
    ];

    // belongsTo Relationships
    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
