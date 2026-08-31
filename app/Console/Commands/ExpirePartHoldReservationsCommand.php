<?php

namespace App\Console\Commands;

use App\Services\ExpirePartHoldReservations;
use Illuminate\Console\Command;

class ExpirePartHoldReservationsCommand extends Command
{
    protected $signature = 'requests:expire-reservations';

    protected $description = 'Expire les mises de côté acceptées dont le délai de réservation est dépassé.';

    public function handle(ExpirePartHoldReservations $expirePartHoldReservations): int
    {
        $expiredCount = $expirePartHoldReservations->handle();

        $this->info($expiredCount === 1
            ? '1 demande expirée.'
            : $expiredCount . ' demandes expirées.');

        return self::SUCCESS;
    }
}
