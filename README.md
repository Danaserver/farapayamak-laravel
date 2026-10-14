<div align="center">

# 📱 پکیج جامع فراپیامک برای لاراول (Laravel Farapayamak)

**کامل‌ترین، مدرن‌ترین و پیشرفته‌ترین کتابخانه ارتباط با وب‌سرویس‌های فراپیامک در اکوسیستم لاراول**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/danaserver/farapayamak-laravel.svg?style=for-the-badge&logo=packagist)](https://packagist.org/packages/danaserver/farapayamak-laravel)
[![Software License](https://img.shields.io/badge/license-MIT-green.svg?style=for-the-badge)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-777BB4.svg?style=for-the-badge&logo=php)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-9.x%20|%2010.x%20|%2011.x%20|%2012.x-FF2D20.svg?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Tests Passing](https://img.shields.io/badge/tests-passing-brightgreen.svg?style=for-the-badge&logo=githubactions)](tests)

[English Documentation](#-english-summary) • [گزارش باگ / پیشنهاد](https://github.com/Danaserver/farapayamak-laravel/issues) • [مستندات فراپیامک](https://docs.farapayamak.ir)

</div>

---

## 📑 فهرست مطالب
- [چرا این پکیج؟](#-چرا-این-پکیج)
- [ویژگی‌های کلیدی](#-ویژگیهای-کلیدی)
- [پیش‌نیازها](#-پیشنیازها)
- [نصب و راه‌اندازی](#-نصب-و-راهاندازی)
- [تنظیمات (.env)](#️-تنظیمات-env)
- [آموزش جامع استفاده](#-آموزش-جامع-استفاده)
  - [۱. ارسال بر اساس الگو/پترن خدماتی (عبور از بلک‌لیست)](#۱-ارسال-بر-اساس-الگوپترن-خدماتی-عبور-از-بلکلیست)
  - [۲. دریافت شماره‌های کاربر برای فرم‌ها و Select-Box](#۲-دریافت-شمارههای-کاربر-برای-فرمها-و-select-box)
  - [۳. دریافت لیست الگوهای خدماتی](#۳-دریافت-لیست-الگوهای-خدماتی)
  - [۴. ارسال پیامک متنی ساده، فلش و گروهی](#۴-ارسال-پیامک-متنی-ساده-فلش-و-گروهی)
  - [۵. ارسال متناظر و نظیر به نظیر](#۵-ارسال-متناظر-و-نظیر-به-نظیر)
  - [۶. استعلام مانده اعتبار عددی و ریالی](#۶-استعلام-مانده-اعتبار-عددی-و-ریالی)
  - [۷. ارسال پیامک صوتی (Text-to-Speech)](#۷-ارسال-پیامک-صوتی-text-to-speech)
  - [۸. زمان‌بندی پیامک](#۸-زمانبندی-پیامک)
  - [۹. استعلام وضعیت تحویل (Delivery)](#۹-استعلام-وضعیت-تحویل-delivery)
- [یکپارچگی با نوتیفیکیشن‌های لاراول (Laravel Notification)](#-یکپارچگی-با-نوتیفیکیشنهای-لاراول-laravel-notification)
- [سیستم شبیه‌ساز تست (Fake / Mocking)](#-سیستم-شبیهساز-تست-fake--mocking)
- [دستورات خط فرمان ترمینال (Artisan Commands)](#-دستورات-خط-فرمان-ترمینال-artisan-commands)
- [رویدادهای لاراول (Laravel Events)](#-رویدادهای-لاراول-laravel-events)
- [جدول کدهای خطای فراپیامک](#-جدول-کدهای-خطای-فراپیامک)
- [لایسنس](#-لایسنس)

---

## 💡 چرا این پکیج؟

اکثر پکیج‌های موجود تنها به یک متد ساده ارسال یا یک وب‌سرویس خلاصه شده‌اند. این پکیج به عنوان یک **کیت توسعه پیامکی کامل سازمانی (Enterprise Toolkit)** طراحی شده است تا:
1. ارتباط با هر دو وب‌سرویس **REST** و **SOAP** را به صورت خودکار یا دستی مدیریت کند.
2. با قابلیت **Failover**، در صورت اختلال در سرور REST فراپیامک، بدون وقفه پیام را از طریق درایور SOAP مخابره کند.
3. در محیط‌های تست، بدون هدر رفتن شارژ پنل، سناریوهای تایید هویت را با `Farapayamak::fake()` محک بزند.
4. با قابلیت **Smart Mobile Sanitizer**، تمام فرمت‌های نامتعارف شماره موبایل (شامل کاراکترهای فارسی/عربی، پیش‌شماره‌های `+98`, `0098`, `98`) را خودکار استاندارد کند.

---

## ✨ ویژگی‌های کلیدی

- ⚡ **ارسال بر اساس الگو (BaseServiceNumber)**: ارسال فوق‌سریع کدهای OTP و پیامک‌های اطلاع‌رسانی حتی به خطوطی که دریافت پیامک تبلیغاتی را مسدود کرده‌اند (بلک‌لیست).
- 🏷️ **پشتیبانی از نام مستعار الگوها (Aliasing)**: امکان استفاده از کلماتی مثل `'otp'` به جای به‌خاطرسپردن کدهای عددی قالب‌ها.
- 📋 **استعلام لایو شماره‌های فعال پنل**: خواندن مستقیم خطوط کاربر از سرور فراپیامک جهت نمایش در کنترل‌روم یا Dropdown تنظیمات پنل ادمین.
- 🔔 **کانال رسمی نوتیفیکیشن لاراول (`FarapayamakChannel`)**: ارسال پیامک بدون نیاز به کدنویسی اضافه و تنها با متد `via()` لاراول.
- 🛠️ **۵ دستور اختصاصی Artisan**: استعلام شارژ، مشاهده خطوط، تست پترن و ارسال مستقیم از طریق ترمینال لینوکس/ویندوز.
- 🎙️ **ارسال صوتی (Voice)**: تماس صوتی خودکار و قرائت متن در صورت نیاز.
- 🛡️ **سندباکس امن (`FARAPAYAMAK_SANDBOX`)**: ثبت پیامک‌ها در `storage/logs/laravel.log` در حین توسعه بدون هزینه.
- 🏢 **پشتیبانی از چند اکانت (Multi-Account)**: مدیریت چند پنل مجزا در یک پروژه لاراول.

---

## 📋 پیش‌نیازها

- PHP >= 8.1
- Laravel >= 9.0 (سازگار با لاراول 9، 10، 11 و 12)
- اکستنشن‌های PHP: `curl` و `json` (اکستنشن `soap` اختیاری است)

---

## 📥 نصب و راه‌اندازی

نصب پکیج از طریق کامپوزر:

```bash
composer require danaserver/farapayamak-laravel
```

سرویس پرووایدر و فکاد پکیج به صورت خودکار توسط **Package Auto-Discovery** لاراول شناسایی می‌شوند.

سپس فایل تنظیمات پکیج را منتشر کنید:

```bash
php artisan vendor:publish --tag=farapayamak-config
```

فایل تنظیمات در مسیر `config/farapayamak.php` کپی خواهد شد.

---

## ⚙️ تنظیمات (.env)

اطلاعات احراز هویت دریافتی از پنل فراپیامک را در فایل `.env` وارد نمایید:

```env
# نام کاربری پنل فراپیامک
FARAPAYAMAK_USERNAME=your_username

# رمز عبور پنل یا کلید وبسرویس (API Key)
FARAPAYAMAK_PASSWORD=your_api_key

# شماره خط اختصاصی فرستنده (پیش‌فرض)
FARAPAYAMAK_FROM=500012345678

# درایور انتخابی: rest یا soap (پیش‌فرض: rest)
FARAPAYAMAK_DRIVER=rest

# تغییر مسیر پیامک‌ها به لاگ لاراول برای تست بدون مصرف شارژ (اختیاری)
FARAPAYAMAK_SANDBOX=false

# سوییچ خودکار به SOAP در صورت قطعی REST (اختیاری)
FARAPAYAMAK_FAILOVER=true

# شناسه‌های الگوهای خدماتی تایید شده در پنل (اختیاری)
FARAPAYAMAK_PATTERN_OTP=12345
FARAPAYAMAK_PATTERN_WELCOME=12346
FARAPAYAMAK_PATTERN_TRACKING=12347
```

---

## 🚀 آموزش جامع استفاده

ابتدا کلاس Facade را فراخوانی کنید:

```php
use Farapayamak\Laravel\Facades\Farapayamak;
```

---

### ۱. ارسال بر اساس الگو/پترن خدماتی (عبور از بلک‌لیست)

این متد برای کدهای احراز هویت (OTP)، فاکتور، وضعیت سفارش و خوش‌آمدگویی استفاده می‌شود و **به تمام شماره‌ها حتی خطوطی که تبلیغات را بسته‌اند در کسری از ثانیه تحویل داده می‌شود**:

```php
// روش اول: با استفاده از نام مستعار تعریف شده در config/farapayamak.php
Farapayamak::pattern('otp', '09121234567', ['54821']);

// ارسال با چند متغیر:
Farapayamak::pattern('order_tracking', '09121234567', [
    'علی رضایی',
    'TRK-90823'
]);

// روش دوم: با شناسه عددی مستقیم BodyId پنل:
Farapayamak::pattern(12345, '09121234567', ['54821']);
```

---

### ۲. دریافت شماره‌های کاربر برای فرم‌ها و Select-Box

برای اینکه در پنل ادمین (مثلاً Filament یا فرم تنظیمات) لیست شماره خطوط فعال شما به کاربر نمایش داده شود تا خط فرستنده را انتخاب کند:

```php
$numbers = Farapayamak::getUserNumbers();
// خروجی یک آرایه تمیز از شماره‌ها است:
// ['50001234567', '50009876543', '02188888888']
```

**نمونه استفاده در کنترلر و قالب Blade لاراول:**
```php
public function settings()
{
    $senderNumbers = Farapayamak::getUserNumbers();
    return view('admin.sms-settings', compact('senderNumbers'));
}
```
```html
<select name="default_sms_line" class="form-control">
    @foreach($senderNumbers as $number)
        <option value="{{ $number }}">{{ $number }}</option>
    @endforeach
</select>
```

---

### ۳. دریافت لیست الگوهای خدماتی

برای مشاهده لیست الگوهایی که در فایل کانفیگ برای پروژه تنظیم کرده‌اید:

```php
$patterns = Farapayamak::getPatterns();
```

---

### ۴. ارسال پیامک متنی ساده، فلش و گروهی

```php
// ارسال پیامک ساده به یک گیرنده
Farapayamak::send('09121234567', 'سلام، سفارش شما دریافت شد.');

// ارسال به چند شماره همزمان (گروهی)
Farapayamak::send(['09121111111', '09122222222'], 'پیامک همگانی برای مشتریان');

// ارسال با شماره فرستنده اختصاصی دیگر
Farapayamak::send('09121234567', 'متن پیامک', from: '3000...');

// ارسال پیامک فلش (Flash SMS: پیامی که مستقیماً روی صفحه گوشی باز می‌شود)
Farapayamak::send('09121234567', 'کد امنیتی فوری: 7721', isFlash: true);
```

---

### ۵. ارسال متناظر و نظیر به نظیر

ارسال متن‌های متفاوت به شماره‌های متفاوت در یک ریکوئست:

```php
Farapayamak::sendMultiple(
    to: [
        '09121111111',
        '09122222222'
    ],
    messages: [
        'آقای رحیمی، بدهی شما ۵۰,۰۰۰ تومان است.',
        'آقای کاظمی، بدهی شما ۱۲۰,۰۰۰ تومان است.'
    ]
);
```

---

### ۶. استعلام مانده اعتبار عددی و ریالی

```php
// مانده تعداد پیامک
$creditCount = Farapayamak::getCredit();

// مانده ریالی کیف پول پنل
$creditRials = Farapayamak::getCreditRial();
```

---

### ۷. ارسال پیامک صوتی (Text-to-Speech)

اگر مخاطب شماره ثابت باشد یا قصد داشته باشید متن به گفتار تبدیل شده و برای او خوانده شود:

```php
Farapayamak::voice('09121234567', 'کد فعال‌سازی شما پنج، شش، هفت است');
```

---

### ۸. زمان‌بندی پیامک

ارسال پیامک در تاریخ و ساعت مشخص در آینده:

```php
Farapayamak::schedule('09121234567', 'یادآوری جلسه فردا', '2026-11-05 09:30:00');
```

---

### ۹. استعلام وضعیت تحویل (Delivery)

```php
// وضعیت تحویل تکی
$deliveryStatus = Farapayamak::getDelivery($recId);

// وضعیت تحویل گروهی
$deliveries = Farapayamak::getDeliveries([$recId1, $recId2]);
```

---

## 🔔 یکپارچگی با نوتیفیکیشن‌های لاراول (Laravel Notification)

به سادگی از طریق کلاس‌های استاندارد `Notification` لاراول پیامک بفرستید:

```bash
php artisan make:notification VerifyMobileNotification
```

سپس در کلاس نوتیفیکیشن خود:

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Farapayamak\Laravel\Channels\FarapayamakChannel;
use Farapayamak\Laravel\Messages\FarapayamakMessage;

class VerifyMobileNotification extends Notification
{
    public function __construct(protected string $code) {}

    public function via($notifiable)
    {
        return [FarapayamakChannel::class];
    }

    public function toFarapayamak($notifiable)
    {
        // حالت ۱: ارسال بر اساس پترن خدماتی (پیشنهادی)
        return FarapayamakMessage::create()
            ->pattern('otp', [$this->code]);

        // حالت ۲: ارسال پیامک متنی ساده
        // return FarapayamakMessage::create("کد تایید شما: {$this->code}");
    }
}
```

ارسال نوتیفیکیشن در کنترلر:

```php
$user->notify(new VerifyMobileNotification('94821'));
```

> **نکته هوشمند**: شماره همراه کاربر به صورت خودکار از فیلدهای `$user->mobile` یا `$user->phone_number` یا از طریق متد `routeNotificationForFarapayamak()` خوانده می‌شود.

---

## 🧪 سیستم شبیه‌ساز تست (Fake / Mocking)

هنگام نوشتن تست‌های خودکار با **PHPUnit** یا **Pest**، برای جلوگیری از ارسال پیامک واقعی و مصرف شارژ:

```php
use Farapayamak\Laravel\Facades\Farapayamak;

public function test_user_receives_otp_on_registration()
{
    Farapayamak::fake();

    // اجرای روت یا سرویس ثبت نام
    $response = $this->postJson('/api/register', [
        'mobile' => '09121234567',
        'name'   => 'علی',
    ]);

    $response->assertOk();

    // بررسی اینکه پترن با شماره و متغیر مورد نظر ارسال شده باشد
    Farapayamak::assertPatternSent('otp', '09121234567');

    // یا بررسی عدم ارسال هرگونه پیامک
    // Farapayamak::assertNothingSent();
}
```

---

## 💻 دستورات خط فرمان ترمینال (Artisan Commands)

| دستور Artisan | توضیحات کاربردی |
| :--- | :--- |
| `php artisan farapayamak:numbers` | دریافت لایو شماره‌های فعال خطوط پنل از سرور و نمایش جدول با مشخص کردن خط پیش‌فرض |
| `php artisan farapayamak:patterns` | نمایش جدول الگوهای خدماتی تعریف‌شده، شناسه‌ها و متغیرهای هرکدام |
| `php artisan farapayamak:credit` | استعلام لایو تعداد پیامک باقی‌مانده و موجودی ریالی پنل |
| `php artisan farapayamak:send {to} {text}` | ارسال تستی پیامک ساده از سرور با قابلیت `--flash` یا `--from` |
| `php artisan farapayamak:test-pattern {pattern} {to} {vars*}` | تست ارسال پیامک خدماتی الگو از ترمینال |

---

## 📡 رویدادهای لاراول (Laravel Events)

پکیج به طور خودکار در مراحل مختلف ایونت‌های لاراول را پرتاب می‌کند تا بتوانید گزارش‌ها را در دیتابیس لاگ کنید:

- `Farapayamak\Laravel\Events\FarapayamakSending`: قبل از ارسال پیامک
- `Farapayamak\Laravel\Events\FarapayamakSent`: پس از ارسال موفق همراه با شناسه پیامک و پاسخ سرور
- `Farapayamak\Laravel\Events\FarapayamakFailed`: در صورت بروز خطا با خطای مربوطه
- `Farapayamak\Laravel\Events\FarapayamakMessageReceived`: دریافت پیامک ورودی از مخاطبان

---

## ⚠️ جدول کدهای خطای فراپیامک

تمامی کدهای خطای وب‌سرویس فراپیامک به پیام‌های فارسی خوانا تبدیل شده و در صورت بروز خطا به شکل `FarapayamakException` برگردانده می‌شوند:

| کد | شرح خطا |
| :---: | :--- |
| **0** | نام کاربری یا رمز عبور (کلید وب‌سرویس) اشتباه است. |
| **2** | اعتبار پنل پیامک کافی نیست. |
| **7** | متن پیامک حاوی کلمات فیلترشده است. |
| **10** | کاربر در سامانه غیرفعال است. |
| **18** | شماره موبایل گیرنده نامعتبر است. |
| **-111** | آی‌پی درخواست‌کننده مجاز نیست (باید در پنل فراپیامک ثبت شود). |
| **-5** | متغیرهای ارسالی با الگوی خدماتی تایید شده همخوانی ندارد. |
| **-4** | شناسه الگو (BodyId) نامعتبر است یا هنوز تایید نشده است. |

---

## 🌐 English Summary

A comprehensive, enterprise-ready SMS integration package for **Laravel** using **Farapayamak SMS Gateway**. Features full support for **REST API**, **SOAP Web Services**, **Shared Base Service Patterns (bypassing telecommunication blacklists)**, **Laravel Notification Channels**, **Testing Fake (Mocking)**, and **Artisan Console Commands**.

---

## 📜 لایسنس

این نرم‌افزار تحت لایسنس [MIT](LICENSE) منتشر شده است.
