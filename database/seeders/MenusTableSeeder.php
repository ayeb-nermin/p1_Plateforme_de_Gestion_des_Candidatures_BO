<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuTranslation;
use Illuminate\Database\Seeder;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(! Menu::where('module_reference', 'home')->first()) {
            if($homePage = Menu::create([
               'module_reference' => 'home',
               'is_active' => 1,
            ])) {
                $languages = languages();
                if ($languages) {
                    foreach ($languages as $abbr => $language) {
                        MenuTranslation::create([
                            'menu_id' => $homePage->id,
                            'slug' => 'home',
                            'title' => 'Accueil',
                            'locale' => $abbr,
                        ]);
                    }
                }

                $this->command->info('Home page added.');
            }
        } else {
            $this->command->info('Home page already exists.');
        }
    }
}
