<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Banner extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'banners';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
     protected $guarded = ['id'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getField($field = 'title')
    {
        return optional($this->translation)->$field;
    }

    public function url()
    {
        $url = null;
        switch ($this->type) {
            case 2:// external link
                $url = ((strpos($this->external_link, 'http://') === -1 || strpos($this->external_link, 'https://') === -1) ? 'http://' : '').$this->external_link;
                break;
            case 1:// internal link
                $menu = null;
                if ($this->internal_link) {
                    if($this->internal_link != -1) {
                        $menu = Menu::where('status', 1)->where('id', $this->internal_link)->first();
                    } elseif($this->internal_link_text) {
                        $url = url(locale().'/'.$this->internal_link_text);
                    }
                }

                if(empty($url)) {
                    $url = is_null($menu) ? 'javascript:void(0)' : $menu->url();
                }
                break;
        }

        return $url;
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function translation()
    {
        return $this->hasOne(BannerTranslation::class)->where('locale', locale());
    }

    public function translations()
    {
        return $this->hasMany(BannerTranslation::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    /**
     * get grades with translations
     *
     * @param $query
     * @param $where
     * @return mixed
     */
    public function scopeFilter($query, $where)
    {
        $tableTranslation = (new BannerTranslation())->getTable();
        $table            = $this->table;
        $foreignKey       = Str::singular($table);
        $query->select('name', $table.'.id as id')
              ->join($tableTranslation, $table.'.id', '=', $tableTranslation.'.'.$foreignKey.'_id')->where($where)
              ->orderBy($table.'.id', 'ASC')->get();

        return $query->pluck('name', 'id')->toArray();
    }

    /**
     * get all banners
     */
    public function scopeGetAll($query, $menu) {

        $query->where([
            'menu_id' => $menu->menu_id,
            'status' => 1
        ]);

        // search
        if(request()->k) {
            $keyword = request()->k;
            $query->whereHas('translation', function($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                  ->orWhere('description', 'like', "%$keyword%")
                  ->orWhere('content', 'like', "%$keyword%");
            });
        }else {
            $query->whereHas('translation');
        }

        return $query->with('translation');
    }

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
