<?php

namespace Farapayamak\Laravel\Events;

use Illuminate\Queue\SerializesModels;

class FarapayamakSent
{
    use SerializesModels;

    public function __construct(
        public string $type,
        public string|array $to,
        public mixed $response,
        public string $driver
    ) {}
}
