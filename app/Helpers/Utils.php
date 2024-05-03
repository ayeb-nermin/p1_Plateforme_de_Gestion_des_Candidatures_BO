<?php
/**
 * Created by PhpStorm.
 * User: Hassen
 * Date: 02/04/2020
 * Time: 05:55
 */

use App\Models\MenuTranslation;
use Backpack\LangFileManager\app\Models\Language;
use Illuminate\Support\Facades\Lang;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(! function_exists('super')) {
    function super() {
        return backpack_user()->hasRole('super.admin');
    }
}

if(! function_exists('front_dir')) {
    function front_dir() {
        return config('cms.front_views_folder');
    }
}

if(! function_exists('front_url')) {
    function front_url($link = null) {
        if(! $link) {
            return url(app()->getLocale());
        }

        return url(app()->getLocale().'/'.$link);
    }
}

if(! function_exists('front_assets')) {
    function front_assets($path = null) {
        if($path) {
            if(app()->getLocale() == 'ar') {
                return asset('assets/ar/'.$path);
            } else {
                return asset('assets/'.$path);
            }
        }

        return '';
    }
}

if(! function_exists('languages')) {
    function languages() {
        if (session()->has('languages')) {
            return session('languages');
        } else {
            if(! $languages = Language::where('active', 1)->orderBy('default', 'DESC')->pluck('name', 'abbr')->toArray()) {
                $languages = config('cms')['languages'];
            }
            session()->flash('languages', $languages);

            return $languages;
        }
    }
}

// get default locale from database only
if(! function_exists('default_language')) {
    function default_language() {
        $defaultLangue = Language::where(['active' => 1, 'default' => 1])->first();
        $language = locale();
        if($defaultLangue) {
            $language = $defaultLangue->abbr;
        }

        return $language;
    }
}

// get default locale from url or database
if(! function_exists('locale')) {
    /**
     * Get the current used locale.
     *
     * @return string Example: 'ar'
     */
    function locale() {
        $locale = app()->getLocale();
        if(! isAdmin()) {// if not admin
            $locale = request()->segment(1);
            if(empty($locale)) {
                if($defaultLocaleDB = Language::where(['active' => 1, 'default' => 1])->first()) {
                    $locale = $defaultLocaleDB->abbr;
                }
            }
        }

        return $locale;
    }
}

if(! function_exists('isAdmin')) {
    function isAdmin() {
        return (request()->segment(1) === config('cms.admin_prefix'));
    }
}

if(! function_exists('get_specific_translation')) {
    function get_specific_translation($locale, $key) {
        return Lang::get($key,[],$locale);
    }
}

if(! function_exists('check_phone')) {
    function check_phone($phone)
    {
        if(strpos($phone, "+216") === 0) {
            $phone = substr($phone, 4);
        } elseif(strpos($phone, "216") === 0) {
            $phone = substr($phone, 3);
        } elseif(strpos($phone, "+(216)") === 0) {
            $phone = substr($phone, 6);
        }

        return $phone;
    }
}

if(! function_exists('check_file')) {
    function check_file($file, $getPath = false) {
        $explode = explode('/', $file);//TODO find a solution to eliminate the name of the upload directory when selecting an element
        if (count($explode)) {
            unset($explode[0]);
            $file = implode('/', $explode);
        }
        if(! $getPath) {
            return is_file(config('apex.dir_upload').'/'.$file);
        } else {
            return url(config('apex.dir_upload').'/'.$file);
        }
    }
}

if(! function_exists('get_menus')) {
    function get_menus($where = [], $toArray = false) {
        if(! \Illuminate\Support\Facades\Schema::hasTable('menus')) {
            return collect();
        }
        $where = array_merge($where, ['is_active' => 1]);
        if ($toArray) {
            return \App\Models\MenuTranslation::whereHas('menu', function ($query) use ($where) {
                $query->where($where);
            })->where('locale', app()->getLocale())->orderBy('title')->pluck('title', 'menu_id')->toArray();
        } else {
            return \App\Models\Menu::with('translation')->where($where)->orderBy('lft')->get();
        }
    }
}

if(! function_exists('get_modules_that_can_be_menus')) {
    function get_modules_that_can_be_menus() {
        $references = [];

        foreach (config('cms.modules') as $module) {
            if ($module['show_only_menu']) {
                $references[$module['reference']] = $module['reference'];
            }
        }

        return $references;
    }
}

if(! function_exists('get_modules_that_has_elements')) {
    function get_modules_that_has_elements() {
        $references = [];

        foreach (config('cms.modules') as $module) {
            if ($module['has_element_assignment']) {
                $references[$module['reference']] = $module['reference'];
            }
        }

        return $references;
    }
}

if (! function_exists('send_mail')) {
    function send_mail($to, $subject, $data, $view ,$files=[])
    {
        $mail = new PHPMailer(true);
        $sended = false;
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com'); // Set the SMTP server to send through
            if ('true' == env('MAIL_SMTPAuth')) {
                $mail->SMTPAuth = true; // Enable SMTP authentication
                $mail->Username = env('MAIL_USERNAME');  // SMTP username
                $mail->Password = env('MAIL_PASSWORD'); // SMTP password
            }
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', false); // Enable TLS encryption; PHPMailer::ENCRYPTION_SMTPS encouraged
            $mail->Port = env('MAIL_PORT', 587); // TCP port to connect to, use 465 for PHPMailer::ENCRYPTION_SMTPS above

            // Add address
            if (is_array($to)) {
                foreach ($to as $adr) {
                    $mail->addAddress($adr);
                }
            } else {
                if (isset($data['user']) && ! empty($data['user'] && optional($data['user'])->name)) {
                    $mail->addAddress($to, $data['user']->name);
                } else {
                    $mail->addAddress($to);
                }
            }

            //Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'BIAT'));

            // Content
            $content = view('emails.'.$view, compact('data'))->render();
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject; //subject
            $mail->Body = $content;
            $mail->AltBody = $content;
            $mail->CharSet = env('MAIL_CHARSET', 'utf-8');

            // attachement file
            if (! empty($files)) {
                foreach ($files as $file) {
                    $mail->AddAttachment($file);
                }
            }

            if ($mail->send()) {
                $sended = true;
            }
            //    dd($sended);
            //    dd($mail);
            //    dd('end debug');

            return $sended;
        } catch (Exception $e) {
            \Log::alert("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        return false;
    }
}


if(! function_exists('getMenuOptions')) {
    function getMenuOptions($moduleName)
    {
        return MenuTranslation::whereHas('menu', function ($q) use($moduleName) {
            $q->where('module_reference', $moduleName);
        })
            ->where('locale', locale())
            ->get()
            ->pluck('title', 'menu_id');
    }
}
