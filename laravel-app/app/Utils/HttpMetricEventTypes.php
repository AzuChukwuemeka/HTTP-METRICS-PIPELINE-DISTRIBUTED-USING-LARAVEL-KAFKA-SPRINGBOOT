<?php

namespace App\Utils;

class HttpMetricEventTypes
{
    const CLICK = 'click-event';
    const VIEW = 'view_event';
    const PURCHASE = 'purchase_event';

    public static function all() : array{
        return [
            self::CLICK,
            self::VIEW,
            self::PURCHASE,
        ];
    }

}
