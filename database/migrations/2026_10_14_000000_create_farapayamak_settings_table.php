<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = config('farapayamak.database.table', 'farapayamak_settings');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('username')->nullable()->comment('نام کاربری پنل فراپیامک');
                $table->text('password')->nullable()->comment('رمز عبور یا کلید وب‌سرویس API Key');
                $table->string('from', 50)->nullable()->comment('شماره خط اختصاصی فرستنده پیش‌فرض');
                $table->string('driver', 20)->default('rest')->comment('درایور پیش‌فرض: rest یا soap');
                $table->boolean('sandbox')->default(false)->comment('حالت سندباکس بدون ارسال واقعی');
                $table->json('patterns')->nullable()->comment('الگوهای خدماتی و متغیرها');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('farapayamak.database.table', 'farapayamak_settings');
        Schema::dropIfExists($tableName);
    }
};
