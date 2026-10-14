<?php

namespace Farapayamak\Laravel\Events;

use Illuminate\Queue\SerializesModels;

class FarapayamakSending
{
    use SerializesModels;

    public function __construct(
        public string $type,
        public string|array $to,
        public mixed $payload,
        public string $driver
    ) {}
}
