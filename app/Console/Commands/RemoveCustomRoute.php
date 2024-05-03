<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class RemoveCustomRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:remove-custom-route
                                {code : HTML/PHP code that registers a route. Use either single quotes or double quotes. Never both. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove HTML/PHP code from the routes/backpack/custom.php file';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $path = 'routes/backpack/custom.php';
        $disk_name = config('backpack.base.root_disk_name');
        $disk = Storage::disk($disk_name);
        $code = $this->argument('code');

        if ($disk->exists($path)) {
            $old_file_path = $disk->path($path);

            // insert the given code before the file's last line
            $file_lines = file($old_file_path, FILE_IGNORE_NEW_LINES);

            // if the code already exists in the file, abort
            $line_numbers = $this->getLastLineNumberThatContains($code, $file_lines);

            if ($line_numbers) {
                foreach ($line_numbers as $line_number) {
                     $file_lines[$line_number] = '';
                    $file_lines[$line_number] = preg_replace("/\r|\n/", "",  $file_lines[$line_number]);
                }

                $new_file_content = implode(PHP_EOL, $file_lines);

                if ($disk->put($path, $new_file_content)) {
                    $this->info('Successfully removed code from ' . $path);
                } else {
                    $this->error('Could not remove from file: ' . $path);
                }
            }
        } else {
            Artisan::call('vendor:publish', ['--provider' => 'Backpack\CRUD\BackpackServiceProvider', '--tag' => 'custom_routes']);

            $this->handle();
        }
    }

    /**
     * Parse the given file stream and return the line number where a string is found.
     *
     * @param string $needle The string that's being searched for.
     * @param array $haystack The file where the search is being performed.
     * @return bool|int The last line number where the string was found. Or false.
     */
    private function getLastLineNumberThatContains($needle, $haystack)
    {
        $matchingLines = array_filter($haystack, function ($k) use ($needle) {
            return strpos($k, $needle) !== false;
        });

        return array_keys($matchingLines);
    }
}
