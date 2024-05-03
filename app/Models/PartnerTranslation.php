<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PartnerTranslation extends Model
{
    use SoftDeletes;
    use CrudTrait;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'partner_id',
        'locale',
        'title',
    ];

    // belongsTo Relationships

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
