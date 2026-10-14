<?php

namespace Farapayamak\Laravel\Console;

use Farapayamak\Laravel\Facades\Farapayamak;
use Illuminate\Console\Command;

class PatternsCommand extends Command
{
    protected $signature = 'farapayamak:patterns';
    protected $description = 'List all defined service templates/patterns in configuration';

    public function handle(): int
    {
        $patterns = Farapayamak::getPatterns();

        if (empty($patterns)) {
            $this->warn("هیچ الگوی خدماتی در فایل config/farapayamak.php ثبت نشده است.");
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($patterns as $alias => $info) {
            $vars = isset($info['vars']) && is_array($info['vars']) ? implode(', ', $info['vars']) : '-';
            $rows[] = [
                'نام مستعار (Alias)' => $alias,
                'شناسه الگو (BodyId)' => $info['id'] ?? '-',
                'عنوان' => $info['title'] ?? '-',
                'متغیرها' => $vars,
            ];
        }

        $this->table(['نام مستعار (Alias)', 'شناسه الگو (BodyId)', 'عنوان', 'متغیرها'], $rows);
        $this->info("برای ارسال پیامک بر اساس الگو می‌توانید از نام مستعار یا کد عددی BodyId استفاده کنید.");

        return self::SUCCESS;
    }
}
