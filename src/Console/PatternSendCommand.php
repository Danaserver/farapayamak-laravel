<?php

namespace Farapayamak\Laravel\Console;

use Farapayamak\Laravel\Facades\Farapayamak;
use Illuminate\Console\Command;
use Throwable;

class PatternSendCommand extends Command
{
    protected $signature = 'farapayamak:test-pattern {pattern : نام مستعار الگو یا شناسه عددی BodyId} {to : شماره موبایل گیرنده} {variables* : مقادیر متغیرها به ترتیب}';
    protected $description = 'Send a service pattern SMS (BaseServiceNumber) for testing';

    public function handle(): int
    {
        $pattern = $this->argument('pattern');
        $to = $this->argument('to');
        $vars = $this->argument('variables');

        $this->info("در حال ارسال پیامک خدماتی الگو [{$pattern}] به {$to}...");

        try {
            $response = Farapayamak::pattern($pattern, $to, $vars);
            $this->info("پیامک خدماتی با موفقیت ارسال شد!");
            $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("خطا در ارسال پیامک خدماتی: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
