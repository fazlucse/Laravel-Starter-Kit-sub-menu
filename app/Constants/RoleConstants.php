<?php

namespace App\Constants;

class RoleConstants
{
    // Define unique constants for each role
    public const DEVELOPER       = 'Developer';
    public const SUPER_ADMIN     = 'Super Admin';
    public const ADMIN           = 'Admin';
    public const HR_MANAGER      = 'HR Manager';
    public const DIVISION_HEAD   = 'Division Head';
    public const DEPARTMENT_HEAD = 'Department Head';
    public const SUPERVISOR      = 'Supervisor'; // Added Supervisor
    public const EMPLOYEE        = 'Employee';
    public const Account        = 'Account';

    /**
     * Full list of roles for system setup and Access Control UI.
     */
    public const ALL = [
        self::DEVELOPER,
        self::SUPER_ADMIN,
        self::ADMIN,
        self::HR_MANAGER,
        self::DIVISION_HEAD,
        self::DEPARTMENT_HEAD,
        self::SUPERVISOR, // Added to the UI/Seeder list
        self::EMPLOYEE,
        self::Account,
    ];

    /**
     * Roles that bypass the "VisibleTo" filters.
     * Supervisors are NOT here because they should only see their own team.
     */
    public const FULL_ACCESS = [
        self::DEVELOPER,
        self::SUPER_ADMIN,
        self::ADMIN,
        self::HR_MANAGER,
        self::DIVISION_HEAD,
    ];
}
