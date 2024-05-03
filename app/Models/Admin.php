<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use CrudTrait;
    use HasRoles;
    use Notifiable;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'admins';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /**
     * Send the given notification.
     *
     * PS: This override the default laravel notify function to replace
     * the \Backpack\CRUD\app\Notifications\ResetPasswordNotification notification.
     *
     * @param  mixed  $notification
     *
     * @return void
     */
    public function notify(\Illuminate\Notifications\Notification $notification)
    {
        if ($notification instanceof \Backpack\CRUD\app\Notifications\ResetPasswordNotification) {
            $notification = new AdminResetPasswordNotification($notification->token, $this->email);
        }

        app(Dispatcher::class)->send($this, $notification);
    }
}
