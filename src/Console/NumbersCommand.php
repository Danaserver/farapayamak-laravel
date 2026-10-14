<?php

namespace Farapayamak\Laravel\Console;

use Farapayamak\Laravel\Facades\Farapayamak;
use Illuminate\Console\Command;
use Throwable;

class NumbersCommand extends Command
{
    protected $signature = 'farapayamak:numbers';
    protected $description = 'Fetch and display the list of active sender numbers from Farapayamak';

    public function handle(): int
    {
        $this->info("در حال استعلام شماره‌های خطوط فعال از سامانه فراپیامک...");

        try {
            $numbers = Farapayamak::getUserNumbers();

            if (empty($numbers)) {
                $this->warn("هیچ شماره خطی برای این حساب یافت نشد یا دسترسی وب‌سرویس غیرفعال است.");
                return self::FAILURE;
            }

            $defaultFrom = config('farapayamak.from');
            $rows = [];

            foreach ($numbers as $index => $number) {
                $isDefault = ((string) $number === (string) $defaultFrom) ? '✔ (پیش‌فرض)' : '-';
                $rows[] = [
                    'ردیف' => $index + 1,
                    'شماره خط' => $number,
                    'وضعیت در کانفیگ' => $isDefault,
                ];
            }

            $this->table(['ردیف', 'شماره خط', 'وضعیت در کانفیگ'], $rows);
            $this->info("تعداد کل خطوط: " . count($numbers));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("خطا در استعلام شماره‌ها: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
