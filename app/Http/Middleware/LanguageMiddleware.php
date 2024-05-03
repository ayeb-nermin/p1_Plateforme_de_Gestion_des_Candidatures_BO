<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(isAdmin()) {
            if($locale = optional(json_decode(optional(backpack_user())->settings))->locale) {
                app()->setLocale($locale);
            }
            if(in_array(app()->getLocale(), ['ar'])) {
                app()->config['backpack.base.html_direction'] = 'rtl';
            }

            $custom_styles = ['assets/css/bootstrap4-toggle.min.css', 'assets/css/style-rtl.css'];
            if ('ar' == app()->getLocale()) {
                $custom_styles = [
                    'assets/ar/css/bootstrap4-toggle.min.css',
                    'packages/backpack/base/css/app-ar.css',
                    'assets/ar/css/style-rtl.css',
                ];
            }
            Config::set('backpack.base.styles', array_merge(Config::get('backpack.base.styles'), $custom_styles));

            $custom_scripts = ['assets/js/bootstrap4-toggle.min.js'];
            if ('ar' == app()->getLocale()) {
                $custom_scripts = [
                    'assets/ar/js/bootstrap4-toggle.min.js',
                ];
            }
            Config::set('backpack.base.scripts', array_merge(Config::get('backpack.base.scripts'), $custom_scripts));

        }

        return $next($request);
    }
}
