<?php

use App\Services\SupabaseClient;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$supabase = app(SupabaseClient::class);
$presetsFiltered = $supabase->select('part_presets', ['*'], ['is_active' => 'true']);
echo 'Total filtered: '.count($presetsFiltered)."\n";
foreach ($presetsFiltered as $p) {
    if ($p['type'] === 'structural') {
        echo "Filtered structural: {$p['name']}\n";
    }
}
