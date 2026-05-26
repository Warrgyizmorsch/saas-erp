<?php

return [

    'name' => 'HRMS',

    'icon' => 'feather-user-check',

    'items' => [

        [
            'icon' => 'feather-home',
            'title' => 'HRMS Dashboard',
            'route' => '/hrms'
        ],

        [
            'title' => 'Employees',
            'icon' => 'feather-users',
            'children' => [
                [
                    'title' => 'Add Employee',
                    'route' => '/hrms/employees/create'
                ],
                [
                    'title' => 'View List',
                    'route' => '/hrms/employees'
                ]
            ]
        ],

        [
            'title' => 'Payroll & Attendance',
            'icon' => 'feather-file-text',
            'children' => [
                [
                    'title' => 'Payroll Admin',
                    'route' => '/hrms/payroll'
                ],
                [
                    'title' => 'Attendance List',
                    'route' => '/hrms/payroll/attendance'
                ]
            ]
        ],

        [
            'title' => 'Master Settings',
            'icon' => 'feather-database',
            'children' => [
                [
                    'title' => 'Departments',
                    'route' => '/hrms/master/departments'
                ],
                [
                    'title' => 'Designations',
                    'route' => '/hrms/master/designations'
                ]
            ]
        ],

        [
            'title' => 'Leave Module',
            'icon' => 'feather-calendar',
            'children' => [
                [
                    'title' => 'Holiday List',
                    'route' => '/hrms/holidays'
                ],
                [
                    'title' => 'Leave Allotment',
                    'route' => '/hrms/leave/allotment'
                ],
                [
                    'title' => 'Leave Applications',
                    'route' => '/hrms/leave/history'
                ]
            ]
        ],

        [
            'title' => 'Project Module',
            'icon' => 'feather-briefcase',
            'children' => [
                [
                    'title' => 'Projects',
                    'route' => '/hrms/projects'
                ],
                [
                    'title' => 'Daily Tasks',
                    'route' => '/hrms/daily-tasks'
                ]
            ]
        ],

        [
            'title' => 'Job Vacancy',
            'icon' => 'feather-user-x',
            'route' => '/hrms/job-vacancy'
        ],

        [
            'title' => 'Celebrations',
            'icon' => 'feather-gift',
            'route' => '/hrms/celebrations'
        ]

    ]

];