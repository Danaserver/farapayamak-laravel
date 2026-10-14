# پکیج جامع فراپیامک برای لاراول (Laravel Farapayamak)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farapayamak/laravel-farapayamak.svg?style=flat-square)](https://packagist.org/packages/farapayamak/laravel-farapayamak)
[![Total Downloads](https://img.shields.io/packagist/dt/farapayamak/laravel-farapayamak.svg?style=flat-square)](https://packagist.org/packages/farapayamak/laravel-farapayamak)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

کامل‌ترین و جامع‌ترین پکیج ارتباط با وب‌سرویس‌های **فراپیامک (Farapayamak)** برای فریم‌ورک **لاراول (Laravel 9, 10, 11, 12)** شامل پشتیبانی از وب‌سرویس‌های **REST**, **SOAP**, **ارسال بر اساس الگو/پترن خدماتی (BaseServiceNumber)**، **سیستم نوتیفیکیشن لاراول (Notification Channel)**، **شبیه‌ساز تست (Fake/Mocking)** و **دستورات ترمینال (Artisan Commands)**.

---

## 🚀 ویژگی‌های کلیدی

- ✅ **پشتیبانی کامل از REST API و SOAP Web Service**
- ✅ **ارسال سریع بر اساس الگو (خط خدماتی اشتراکی / BaseServiceNumber) بدون مسدودی بلک‌لیست**
- ✅ **پشتیبانی از نام مستعار الگوها (Pattern Aliases)**: تعریف نام‌هایی مثل `otp` یا `welcome` بدون نیاز به حفظ کردن کدهای عددی
- ✅ **دریافت لایو لیست شماره‌های خطوط فعال کاربر از سرور (`getUserNumbers`)**
- ✅ **کانال اختصاصی نوتیفیکیشن لاراول (`FarapayamakChannel`)**
- ✅ **اصلاح و پاک‌سازی هوشمند شماره موبایل ایران (تبدیل اعداد فارسی، +98، 0098 و ...)**
- ✅ **حالت آزمایشی و شبیه‌ساز تست (`Farapayamak::fake()`) برای PHPUnit و Pest**
- ✅ **حالت سندباکس و لاگ (`FARAPAYAMAK_SANDBOX=true`) بدون مصرف شارژ در محیط توسعه**
- ✅ **سوییچ اضطراری خودکار (Automatic REST to SOAP Failover)**
- ✅ **دستورات کاربردی ترمینال (Artisan Commands): استعلام شارژ، مشاهده خطوط، تست الگوها و ارسال**
- ✅ **ارسال پیامک صوتی و تبدیل متن به گفتار (TTS)**
- ✅ **رویدادهای لاراول (Laravel Events) برای ثبت تاریخچه و لاگ در دیتابیس**
- ✅ **پشتیبانی از چند پنل و خط همزمان (Multi-Account)**

---

## 📦 نصب و راه‌اندازی

نصب پکیج از طریق کامپوزر:

```bash
composer require farapayamak/laravel-farapayamak
```

انتشار فایل تنظیمات در لاراول:

```bash
php artisan vendor:publish --tag=farapayamak-config
```

فایل تنظیمات در مسیر `config/farapayamak.php` ایجاد می‌شود.

---

## ⚙️ تنظیمات محیطی (.env)

کلیدهای زیر را به فایل `.env` پروژه خود اضافه کنید:

```env
# نام کاربری پنل فراپیامک
FARAPAYAMAK_USERNAME=your_username

# رمز عبور پنل یا کلید وبسرویس (API Key)
FARAPAYAMAK_PASSWORD=your_api_key

# شماره خط اختصاصی پیش‌فرض فرستنده
FARAPAYAMAK_FROM=5000...

# درایور پیش‌فرض: rest یا soap
FARAPAYAMAK_DRIVER=rest

# فعال‌سازی حالت سندباکس در محیط لوکال (اختیاری)
FARAPAYAMAK_SANDBOX=false

# شناسه‌های الگوهای خدماتی (اختیاری)
FARAPAYAMAK_PATTERN_OTP=12345
FARAPAYAMAK_PATTERN_WELCOME=12346
FARAPAYAMAK_PATTERN_TRACKING=12347
```

---

## 📖 نحوه استفاده

### ۱. ارسال بر اساس الگو / پترن خدماتی (BaseServiceNumber)
ارسال سریع پیامک به خطوط مسدود و بلک‌لیست مخابرات بر اساس قالب‌های تایید شده:

```php
use Farapayamak\Laravel\Facades\Farapayamak;

// با استفاده از نام مستعار تعریف شده در کانفیگ:
Farapayamak::pattern('otp', '09121234567', ['54321']);

// ارسال با چند متغیر:
Farapayamak::pattern('order_tracking', '09121234567', ['علی رضایی', 'TRK-98432']);

// یا استفاده مستقیم از شناسه عددی BodyId الگو:
Farapayamak::pattern(12345, '09121234567', ['54321']);
```

### ۲. استعلام شماره خطوط فعال کاربر (نمایش در سلکت‌باکس)
برای نمایش خطوط در پنل ادمین یا تنظیمات وب‌سایت:

```php
$numbers = Farapayamak::getUserNumbers();
// خروجی: ['5000123456', '5000987654', '0218888888']
```

### ۳. دریافت لیست الگوهای خدماتی تعریف‌شده
```php
$patterns = Farapayamak::getPatterns();
```

### ۴. ارسال پیامک معمولی و فلش
```php
// ارسال به یک شماره
Farapayamak::send('09121234567', 'سلام! سفارش شما تایید شد.');

// ارسال به چند شماره همزمان
Farapayamak::send(['09121111111', '09122222222'], 'پیام همگانی');

// ارسال پیامک فلش (نمایش مستقیم روی صفحه گوشی)
Farapayamak::send('09121234567', 'کد ورود فوری: 1234', isFlash: true);

// ارسال با خط اختصاصی متفاوت
Farapayamak::send('09121234567', 'متن پیامک', from: '3000...');
```

### ۵. ارسال متناظر و نظیر به نظیر (پیام‌های متفاوت به افراد مختلف)
```php
Farapayamak::sendMultiple(
    to: ['09121111111', '09122222222'],
    messages: [
        'آقای احمدی بدهی شما ۱۰۰ تومان است.',
        'آقای مرادی بدهی شما ۲۰۰ تومان است.'
    ]
);
```

### ۶. استعلام اعتبار پیامک و مانده ریالی
```php
// دریافت تعداد پیامک باقی‌مانده
$credit = Farapayamak::getCredit();

// دریافت مانده ریالی
$rial = Farapayamak::getCreditRial();
```

### ۷. زمان‌بندی پیامک
```php
Farapayamak::schedule('09121234567', 'یادآوری تمدید اشتراک', '2026-11-01 10:00:00');
```

### ۸. استعلام وضعیت دلیوری
```php
$status = Farapayamak::getDelivery($recordId);
```

### ۹. پیامک صوتی (Voice / TTS)
```php
// تماس صوتی و خواندن متن پیام
Farapayamak::voice('09121234567', 'کد تایید شما ۵ رقمی است: ۱، ۲، ۳، ۴، ۵');
```

---

## 🔔 استفاده در سیستم نوتیفیکیشن لاراول (Laravel Notification)

برای ارسال پیامک از طریق کلاس‌های `Notification` لاراول، از کانال `FarapayamakChannel` استفاده کنید:

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Farapayamak\Laravel\Channels\FarapayamakChannel;
use Farapayamak\Laravel\Messages\FarapayamakMessage;

class OrderShippedNotification extends Notification
{
    public function via($notifiable)
    {
        return [FarapayamakChannel::class];
    }

    public function toFarapayamak($notifiable)
    {
        // حالت ۱: ارسال بر اساس پترن خدماتی
        return FarapayamakMessage::create()
            ->pattern('order_tracking', [$notifiable->name, 'TRK-12345']);

        // حالت ۲: ارسال پیامک متنی ساده
        // return FarapayamakMessage::create('سفارش شما با موفقیت ارسال شد.');
    }
}
```

شماره موبایل گیرنده به صورت خودکار از متد `routeNotificationForFarapayamak()` یا فیلدهای `$user->mobile` و `$user->phone` استخراج می‌شود.

---

## 🧪 تست و شبیه‌ساز (Testing & Mocking)

هنگام اجرای تست‌های خودکار لاراول، می‌توانید با `Farapayamak::fake()` جلوی ارسال واقعی پیامک را بگیرید:

```php
use Farapayamak\Laravel\Facades\Farapayamak;

public function test_user_receives_otp()
{
    Farapayamak::fake();

    // اجرای فرایند ثبت نام در سایت
    $this->postJson('/api/register', ['mobile' => '09121234567']);

    // بررسی ادعای ارسال الگو
    Farapayamak::assertPatternSent('otp', '09121234567');
}
```

---

## 💻 دستورات خط فرمان ترمینال (Artisan Commands)

| دستور | توضیحات |
| :--- | :--- |
| `php artisan farapayamak:numbers` | دریافت و نمایش جدول شماره‌های فعال خطوط کاربر از سرور |
| `php artisan farapayamak:patterns` | نمایش جدول الگوهای خدماتی، شناسه‌ها و متغیرها |
| `php artisan farapayamak:credit` | استعلام مانده عددی و ریالی پنل پیامک |
| `php artisan farapayamak:send {to} {text}` | ارسال پیامک تستی از ترمینال سرور |
| `php artisan farapayamak:test-pattern {pattern} {to} {vars*}` | تست ارسال پیامک خدماتی الگو از ترمینال |

---

## 📜 لایسنس

این پکیج تحت لایسنس [MIT](LICENSE) به صورت متن‌باز منتشر شده است.
