<?php

namespace Farapayamak\Laravel\Events;

use Illuminate\Queue\SerializesModels;

class FarapayamakMessageReceived
{
    use SerializesModels;

    public function __construct(
        public string $from,
        public string $to,
        public string $message,
        public ?string $msgId = null,
        public array $raw = []
    ) {}
}
