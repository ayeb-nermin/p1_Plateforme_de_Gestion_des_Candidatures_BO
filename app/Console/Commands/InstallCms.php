<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class InstallCms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:install {migrate=yes} {install=yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all database tables and install CMS';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $started_at = new \DateTime(date('Y-m-d H:i:s'));

        $this->clearAll();

        $this->dropAllTablesThenRunMigrations();

        $this->installModules($this->argument('install'), 'partner');

        $this->installModules($this->argument('install'), 'news');

        $this->installModules($this->argument('install'), 'faq');

        $this->installModules($this->argument('install'), 'testimonial');

        $this->installModules($this->argument('install'), 'contact');

        $this->installModules($this->argument('install'), 'email');

        $this->installModules($this->argument('install'), 'locality');

        $this->installModules($this->argument('install'), 'gallery');

        $this->clearAll();

        $this->runMigrations($this->argument('migrate'));

        $this->runSeeders();

        $this->clearAll();

        $this->info("\n CMS successfully installed ");

        $ended_at = new \DateTime(date('Y-m-d H:i:s'));
        $this->info("\n Duration : " . $started_at->diff($ended_at)->i . ' minutes ' . $started_at->diff($ended_at)->s . ' seconds');

    }

    public function runMigrations(string $migrate)
    {
        $migrate = null;

        while (!in_array($migrate, ['yes', 'y', 'no', 'n'])) {
            $migrate = $this->ask('Run migrate:fresh ? (yes/no)', 'yes');
        }

        $this->info('Installing CMS... ');

        if (in_array($migrate, ['yes', 'y'])) {
            $this->dropAllTablesThenRunMigrations();
        } else {
            $this->truncateAllTables();
        }
    }

    public function installModules(string $install, string $module)
    {

        $install = null;

        while (!in_array($install, ['yes', 'y', 'no', 'n'])) {
            $install = $this->ask('Install ' . Str::title($module) . ' module ? (yes/no)', 'yes');
        }

        $this->info('Installing CMS... ');

        if (in_array($install, ['no', 'n', 'N'])) {
            $this->removeModuleFiles($module);
        } else {
            $this->addCustomRoute($module);
        }
    }

    public function clearAll()
    {
        Cache::flush();
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    public function runSeeders()
    {
        Artisan::call('db:seed');
    }

    public function dropAllTablesThenRunMigrations()
    {
        Artisan::call('migrate:fresh'); // Migration paths: AppServiceProvider@setUpMigrationPaths
    }

    public function truncateAllTables()
    {
        $tables = DB::select('SHOW TABLES');
        $db = DB::getDatabaseName();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            if ($table->{'Tables_in_' . $db} != 'migrations') {
                DB::table($table->{'Tables_in_' . $db})->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function addCustomRoute($module)
    {
        $module_route = [
            'partner' => "
    Route::post('partner/{id}/enable/{state}', 'PartnerCrudController@enable')->name('partner.enable');
    Route::crud('partner', 'PartnerCrudController');",
            'news' => "
    Route::crud('news-category', 'NewsCategoryCrudController');
    Route::post('news-category/{id}/enable/{state}', 'NewsCategoryCrudController@enable')->name('news-category.enable');

    Route::crud('news', 'NewsCrudController');
    Route::post('news/{id}/enable/{state}', 'NewsCrudController@enable')->name('news.enable');",
            'faq' => "
    Route::post('faq/{id}/enable/{state}', 'FaqCrudController@enable')->name('faq.enable');
    Route::crud('faq', 'FaqCrudController');

    Route::post('faq-category/{id}/enable/{state}', 'FaqCategoryCrudController@enable')->name('faq-category.enable');
    Route::crud('faq-category', 'FaqCategoryCrudController');",
            'testimonial' => "
    Route::crud('testimonial', 'TestimonialCrudController');
    Route::post('testimonial/{id}/enable/{state}', 'TestimonialCrudController@enable')->name('testimonial.enable');",
            'contact' => "
    Route::crud('contact', 'ContactCrudController');",
            'gallery' => "
    Route::crud('gallery', 'GalleryCrudController');
    Route::post('gallery/{id}/enable/{state}', 'GalleryCrudController@enable')->name('gallery.enable');",
            'email' => "
    Route::crud('email', 'EmailCrudController');",
            'locality' => "
    Route::crud('locality', 'LocalityCrudController');",
        ];

        // Create the route
        $this->call('backpack:add-custom-route', [
            'code' => $module_route[$module],
        ]);
    }

    public function removeCustomRoute($module)
    {
        $nameTitle = ucfirst(Str::camel($module));
        $nameKebab = Str::kebab($nameTitle);
        // Create the route
        $this->call('backpack:remove-custom-route', [
            'code' => $nameKebab,
        ]);
    }

    public function removeModuleFiles($module)
    {
        // Delete route
        $this->removeCustomRoute($module);

        // Delete controller crud
        if (File::exists(app_path('Http/Controllers/Admin/' . Str::title($module) . 'CrudController.php'))) {
            File::delete(app_path('Http/Controllers/Admin/' . Str::title($module) . 'CrudController.php'));
        }

        // Delete request
        if (File::exists(app_path('Http/Requests/' . Str::title($module) . 'Request.php'))) {
            File::delete(app_path('Http/Requests/' . Str::title($module) . 'Request.php'));
        }

        // Delete migration
        $migrations = DB::table('migrations')
            ->select('migration')
            ->where('migration', 'LIKE', '%' . $module . '%')
            ->get();

        if ($migrations) {
            foreach ($migrations as $migration) {
                if (File::exists(database_path('migrations/' . $migration->migration . '.php'))) {
                    File::delete(database_path('migrations/' . $migration->migration . '.php'));
                }
            }
        }

        // Delete model
        if (File::exists(app_path('Models/' . Str::title($module) . '.php'))) {
            File::delete(app_path('Models/' . Str::title($module) . '.php'));
        }

        // Delete sidebar element
        if (File::exists(resource_path('views/vendor/backpack/base/inc/' . Str::title($module) . '.blade.php'))) {
            File::delete(resource_path('views/vendor/backpack/base/inc/' . Str::title($module) . '.blade.php'));
        }
    }
}
