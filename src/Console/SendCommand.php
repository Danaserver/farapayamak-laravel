<?php

namespace Farapayamak\Laravel\Console;

use Farapayamak\Laravel\Facades\Farapayamak;
use Illuminate\Console\Command;
use Throwable;

class SendCommand extends Command
{
    protected $signature = 'farapayamak:send {to : شماره موبایل گیرنده} {text : متن پیامک} {--from= : شماره فرستنده اختصاصی} {--flash : ارسال به صورت پیامک فلش}';
    protected $description = 'Send a test SMS via Farapayamak';

    public function handle(): int
    {
        $to = $this->argument('to');
        $text = $this->argument('text');
        $from = $this->option('from');
        $isFlash = (bool) $this->option('flash');

        $this->info("در حال ارسال پیامک به {$to}...");

        try {
            $response = Farapayamak::send($to, $text, $from, $isFlash);
            $this->info("پیامک با موفقیت ارسال شد!");
            $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("خطا در ارسال پیامک: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
