<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Backpack\LangFileManager\app\Models\Language;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = config('cms.languages');
        foreach($languages as $key => $value) {
            if(! Language::where('abbr', $key)->first()) {
                DB::table('languages')->insert([
                    'name' => $value,
                    'flag' => 'c',
                    'abbr' => $key,
                    'script' => 'Latn',
                    'native' => strtolower($value),
                    'active' => '1',
                    'default' => (($key == app()->getLocale()) ? '1' : '0'),
                ]);
            }
        }

        $this->command->info('Language seeding successful.');
    }
}
