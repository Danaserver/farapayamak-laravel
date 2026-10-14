<?php

namespace Farapayamak\Laravel\Events;

use Illuminate\Queue\SerializesModels;
use Throwable;

class FarapayamakFailed
{
    use SerializesModels;

    public function __construct(
        public string $type,
        public string|array $to,
        public Throwable $exception,
        public string $driver
    ) {}
}
