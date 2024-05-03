<?php

namespace App\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Menu extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'menus';
    protected $fillable = [
        'parent_id',
        'lft',
        'rgt',
        'depth',
        'type',
        'external_link',
        'internal_link',
        'module_reference',
        'target',
        'is_active',
        'image',
        'icon',
        'has_form',
        'admin_id',
        'banners',
        'menu_zone_id',
    ];
    //protected $guarded = ['id'];
    protected $casts = [
        'banners' => 'array'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getMenuType()
    {
        $link = '';
        // 7: member page
        $exceptedModules = ['contact'];
       // $modulesWithoutListing = [1, 6];
        $modulesWithoutListing = [];
        switch ($this->type) {
            case 1:
                $link = (optional($this->menu)->translation && optional($this->menu->translation)->title) ? $this->menu->translation->title : '';
                break;
            case 2:
                $link = ($this->external_link) ? $this->external_link : '';
                break;
            case 3:
                $moduleName = config('cms.modules.' . $this->module_reference . '.reference');
                // not home page
                if (in_array($this->module_reference, $modulesWithoutListing)) {
                    if ($this->module_reference == 'home') {
                        // home page
                        $link = '<a class="btn btn-outline-info btn-xs" href="' . backpack_url($moduleName . '/1/edit?menu_id=' . $this->id) . '">' . trans('form.module.' . $moduleName) . ' <i class="la la-eye"></i></a>';
                    } else {
                        // cms page: go to edit
                        $link = '<a class="btn btn-outline-info btn-xs" href="' . backpack_url($moduleName . '/' . $this->id . '/edit') . '">' . trans('form.module.' . $moduleName) . ' <i class="la la-eye"></i></a>';
                    }
                } elseif (in_array($this->module_reference, $exceptedModules)) {
                    // without menu id
                    $link = '<a class="btn btn-outline-info btn-xs" href="' . backpack_url($moduleName) . '">' . trans('form.module.' . $moduleName) . ' <i class="la la-eye"></i></a>';
                } else {
                    $link = '<a class="btn btn-outline-info btn-xs" href="' . backpack_url($moduleName . '?menu_id=' . $this->id) . '">' . (($moduleName) ? trans('form.module.' . $moduleName) : '') . ' <i class="la la-eye"></i></a>';
                }
                break;
        }

        return '<span class="badge badge-default">' . strip_tags($link) . '</span>';
    }

    /**
     * Get all menu items, in a hierarchical collection.
     * Only supports 2 levels of indentation.
     */
    public static function getTree()
    {
        $menu = self::orderBy('lft')
            ->get();

        if ($menu->count()) {
            foreach ($menu as $k => $menu_item) {
                $menu_item->children = collect([]);

                foreach ($menu as $i => $menu_subitem) {
                    if ($menu_subitem->parent_id == $menu_item->id) {
                        $menu_item->children->push($menu_subitem);

                        // remove the subitem for the first level
                        $menu = $menu->reject(function ($item) use ($menu_subitem) {
                            return $item->id == $menu_subitem->id;
                        });
                    }
                }
            }
        }

        return $menu;
    }

    public function url()
    {
        $url = 'javascript:void(0)';
        if ($this->type) {
            switch ($this->type) {
                case 2: // external link
                    $url = ((strpos($this->external_link, 'http://') === -1 || strpos($this->external_link, 'https://') === -1) ? 'http://' : '') . $this->external_link;
                    break;
                case 1: // internal link
                    $menu = null;
                    if ($this->internal_link) {
                        $menu = Menu::where('is_active', 1)->where('id', $this->internal_link)->first();
                    }
                    $url = is_null($menu) ? 'javascript:void(0)' : $menu->url();
                    break;
                case 3:
                    if ($this->module_reference) {
                        $url = front_url();
                        if (config('cms.modules.' . $this->module_reference) && $this->module_reference != 'home' && optional($this->translation)->slug) {
                            // not home page
                            $url = front_url($this->translation->slug);
                        }
                    } else {
                        $url = front_url($this->translation->slug);
                    }
                    break;
                default:
                    $url = front_url();
                    if ($this->module_reference != 'home' && optional($this->translation)->slug) {
                        // not home page
                        $url = front_url($this->translation->slug);
                    }
            }
        }

        return $url;
    }

    /**
     * @return string
     */
    public function current()
    {
        if (request()->segment(2) == $this->slug) {
            return 'active';
        }

        return '';
    }

    // fix function attribute array issue
    public function isTranslatableAttribute($key): bool
    {
        if (is_array($key)) {
            return false;
        }

        return $this->traitIsTranslatableAttribute($key);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function translation()
    {
        return $this->hasOne(MenuTranslation::class)
            ->where('locale', locale());
    }

    public function translations()
    {
        return $this->hasMany(MenuTranslation::class);
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id')->with('translation');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')
            ->where('is_active', 1)
            ->with('translation')
            ->orderBy('lft', 'ASC');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'internal_link')
            ->with('translation');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function elements($modelName)
    {
        $modelNameSpace = "\App\Models\\" . ucfirst($modelName);

        return $this->belongsToMany($modelNameSpace, 'menu_elements', 'menu_id', 'element_id')->withPivot('order', 'model')->orderBy('menu_elements.order', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE
    |--------------------------------------------------------------------------
    */
}
