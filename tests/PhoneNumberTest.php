<?php

namespace Farapayamak\Laravel\Tests;

use Farapayamak\Laravel\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_it_cleans_standard_mobile(): void
    {
        $this->assertEquals('09121234567', PhoneNumber::clean('09121234567'));
    }

    public function test_it_converts_persian_and_arabic_digits(): void
    {
        $this->assertEquals('09121234567', PhoneNumber::clean('۰۹۱۲۱۲۳۴۵۶۷'));
        $this->assertEquals('09121234567', PhoneNumber::clean('٠٩١٢١٢٣٤٥٦٧'));
    }

    public function test_it_converts_international_prefixes(): void
    {
        $this->assertEquals('09121234567', PhoneNumber::clean('+989121234567'));
        $this->assertEquals('09121234567', PhoneNumber::clean('00989121234567'));
        $this->assertEquals('09121234567', PhoneNumber::clean('989121234567'));
        $this->assertEquals('09121234567', PhoneNumber::clean('9121234567'));
    }

    public function test_it_validates_iranian_mobile_numbers(): void
    {
        $this->assertTrue(PhoneNumber::isValid('09121234567'));
        $this->assertTrue(PhoneNumber::isValid('+989121234567'));
        $this->assertTrue(PhoneNumber::isValid('۰۹۳۵۱۲۳۴۵۶۷'));
        $this->assertFalse(PhoneNumber::isValid('02188888888'));
        $this->assertFalse(PhoneNumber::isValid('12345'));
    }

    public function test_it_cleans_multiple_numbers(): void
    {
        $input = ['+989123456789', '09122222222', '۰۹۳۵۳۳۳۳۳۳۳'];
        $expected = ['09123456789', '09122222222', '09353333333'];

        $this->assertEquals($expected, PhoneNumber::cleanMultiple($input));
    }
}
