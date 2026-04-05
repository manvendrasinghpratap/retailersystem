<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class SyncRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Route::getRoutes();

        $dbRoutes = [];

        foreach ($routes as $route) {

            $method = implode(',', $route->methods());
            $uri = $route->uri();

            $data = [
                'method' => $method,
                'uri' => $uri,
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => implode(',', $route->middleware()),
                'updated_at' => now(),
                'created_at' => now(),
            ];

            // Upsert (Insert or Update)
            if ($uri != 'admin/acl' && $uri != 'admin/acl/*' && !is_null($route->getName()) && $route->getActionName() != 'Closure') {
                DB::table('routes')->updateOrInsert(
                    ['uri' => $uri, 'method' => $method],
                    $data
                );
            }

            $dbRoutes[] = $uri . '|' . $method;
        }

        // ✅ DELETE removed routes (important sync step)
        DB::table('routes')
            ->whereNotIn(DB::raw("CONCAT(uri,'|',method)"), $dbRoutes)
            ->delete();

        $this->info('Routes synced successfully!');
    }
}
