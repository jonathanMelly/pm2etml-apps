<?php

namespace App\Constants;

class RoleName
{
    public const STUDENT = 'eleve';

    public const TEACHER = 'prof';

    public const PRINCIPAL = 'mp';

    public const DEAN = 'doyen';

    public const ADMIN = 'root';

    public const AVAILABLE_ROLES = [
        self::STUDENT,
        self::TEACHER,
        self::PRINCIPAL,
        self::DEAN,
        self::ADMIN
    ];

    public const TEACHER_AND_HIGHER_RANK = [
        self::TEACHER,
        self::PRINCIPAL,
        self::DEAN,
        self::ADMIN
    ];
}
