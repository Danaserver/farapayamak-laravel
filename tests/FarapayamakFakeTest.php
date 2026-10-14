<?php

namespace Farapayamak\Laravel\Tests;

use Farapayamak\Laravel\Testing\FarapayamakFake;
use PHPUnit\Framework\TestCase;

class FarapayamakFakeTest extends TestCase
{
    public function test_it_records_sent_messages_and_asserts(): void
    {
        $fake = new FarapayamakFake();
        $fake->assertNothingSent();

        $fake->send('09121234567', 'Test SMS');
        $fake->assertSentCount(1);

        $fake->assertSent(function ($record) {
            return in_array('09121234567', $record['to']) && $record['text'] === 'Test SMS';
        });
    }

    public function test_it_records_patterns_and_asserts(): void
    {
        $fake = new FarapayamakFake();

        $fake->sendPattern('12345', '09121234567', ['54321', 'Ali']);
        $fake->assertPatternSent('12345', '09121234567', ['54321', 'Ali']);
    }
}
