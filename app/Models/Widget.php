<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Field;

class Widget extends Model
{
    use CrudTrait;
    use Field;

    const HOME_PAGE = 1;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'widgets';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function elements($modelName)
    {
        $modelNameSpace = "\App\Models\\" . $modelName;

        return $this->belongsToMany($modelNameSpace, 'widget_elements', 'widget_id', 'element_id')->withPivot('order', 'model')->orderBy('widget_elements.order', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function translation()
    {
        return $this->hasOne(WidgetTranslation::class, 'widget_id')->where('locale', locale());
    }

    public function translations()
    {
        return $this->hasMany(WidgetTranslation::class, 'widget_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
