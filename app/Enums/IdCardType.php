<?php

namespace App\Enums;

enum IdCardType: string
{
    case DRIVING_LICENSE = 'driving_license';
    case PASSPORT = 'passport';
    case NATIONAL = 'national';

    public static function values()
    {
        $values = [];

        foreach (self::cases() as $props) {
            array_push($values, $props->value);
        }

        return $values;
    }
}