<?php

namespace App\Exceptions;

use RuntimeException;

class SlotUnavailableException extends RuntimeException
{
    public function __construct(
        public readonly string $slot,
        public readonly string $date,
    ) {
        parent::__construct("Slot {$slot} pada {$date} sudah tidak tersedia.");
    }
}
