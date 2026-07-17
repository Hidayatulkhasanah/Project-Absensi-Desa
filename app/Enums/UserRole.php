<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';
    case User = 'user';

    /** Roles PegawaiController::store/update accept (operator accounts are seeded, not self-service). */
    public static function pegawaiFormValues(): array
    {
        return [self::Admin->value, self::User->value];
    }
}
