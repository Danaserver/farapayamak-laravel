<?php

namespace Farapayamak\Laravel\Console;

use Farapayamak\Laravel\Facades\Farapayamak;
use Illuminate\Console\Command;
use Throwable;

class CreditCommand extends Command
{
    protected $signature = 'farapayamak:credit';
    protected $description = 'Check Farapayamak SMS and Rial credit balance';

    public function handle(): int
    {
        $this->info("در حال استعلام اعتبار از پنل فراپیامک...");

        try {
            $count = Farapayamak::getCredit();
            $rial  = Farapayamak::getCreditRial();

            $formattedRial = is_numeric($rial) ? number_format((float) $rial) . ' ریال' : $rial;

            $this->table(
                ['نوع اعتبار', 'میزان مانده'],
                [
                    ['تعداد پیامک', is_numeric($count) ? number_format((float) $count) : $count],
                    ['اعتبار ریالی', $formattedRial],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("خطا در استعلام اعتبار: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
