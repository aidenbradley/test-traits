<?php

namespace Drupal\Tests\test_support\Traits\Support\Time;
use Carbon\Carbon;
use Drupal\Component\Datetime\TimeInterface;

class Time implements TimeInterface
{
    public static function fake(): self
    {
        return new self();
    }

    public function getRequestTime()
    {
        return Carbon::now()->getTimestamp();
    }

    public function getRequestMicroTime()
    {
        return Carbon::now()->getTimestampMs();
    }

    public function getCurrentTime()
    {
        return Carbon::now()->getTimestamp();
    }

    public function getCurrentMicroTime()
    {
        return Carbon::now()->getTimestampMs();
    }
}
