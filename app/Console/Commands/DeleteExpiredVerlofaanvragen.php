<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Verlofaanvraag;

class DeleteExpiredVerlofaanvragen extends Command
{
    protected $signature = 'verlofaanvragen:delete-expired';

    protected $description = 'Verwijder verlopen verlof';

    public function handle()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        Verlofaanvraag::where('datum', '<=', $sevenDaysAgo)->delete();

        $this->info('Verlopen verlof verwijderd.');
    }
}

