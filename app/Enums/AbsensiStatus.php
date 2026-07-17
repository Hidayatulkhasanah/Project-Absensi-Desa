<?php

namespace App\Enums;

enum AbsensiStatus: string
{
    case Hadir = 'Hadir';
    case Izin = 'Izin';
    case Alpha = 'Alpha';
    case Sppd = 'SPPD';
    case Cuti = 'cuti';

    /** Statuses a pegawai may self-report via checkin. */
    public static function checkinValues(): array
    {
        return [self::Hadir->value, self::Izin->value, self::Sppd->value];
    }

    /** Statuses an admin may set via manual store/update (cuti is seeder/DB-only today). */
    public static function adminFormValues(): array
    {
        return [self::Hadir->value, self::Izin->value, self::Alpha->value, self::Sppd->value];
    }
}
