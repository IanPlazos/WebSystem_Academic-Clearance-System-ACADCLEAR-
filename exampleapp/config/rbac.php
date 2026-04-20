<?php

return [
    'roles' => [
        // Tenant admin gets full access to tenant modules by default.
        'school_admin' => ['*'],

        // Staff can only work on operational scopes assigned by office role.
        'staff' => [
            'tenant.dashboard.view',
            'tenant.clearances.view_own',
            'tenant.clearances.update_own',
            'tenant.profile.manage',
        ],

        // Students are limited to self-service views.
        'student' => [
            'tenant.dashboard.view_own',
            'tenant.student.clearances.view',
            'tenant.support_chat.access',
            'tenant.profile.manage',
        ],
    ],

    'modules' => [
        'dashboard' => [
            'tenant.dashboard.view',
        ],
        'plan_requests' => [
            'tenant.plan_requests.view',
            'tenant.plan_requests.approve',
            'tenant.plan_requests.reject',
        ],
        'colleges' => [
            'tenant.colleges.manage',
        ],
        'departments' => [
            'tenant.departments.manage',
        ],
        'students' => [
            'tenant.students.manage',
        ],
        'staff' => [
            'tenant.staff.manage',
        ],
        'reports' => [
            'tenant.reports.view',
            'tenant.reports.export',
        ],
        'clearances' => [
            'tenant.clearances.view',
            'tenant.clearances.create',
            'tenant.clearances.update',
            'tenant.clearances.export',
        ],
        'profile' => [
            'tenant.profile.manage',
        ],
        'support_chat' => [
            'tenant.support_chat.access',
        ],
    ],
];
