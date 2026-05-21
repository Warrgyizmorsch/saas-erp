<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Info
    |--------------------------------------------------------------------------
    */

    'name' => 'CRM',

    'icon' => 'feather-users',

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Items
    |--------------------------------------------------------------------------
    */

    'items' => [

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        [
            'title' => 'CRM Dashboard',
            'icon' => 'feather-home',
            'route' => 'crm.dashboard',
        ],

        /*
        |--------------------------------------------------------------------------
        | LEADS
        |--------------------------------------------------------------------------
        */

        [
            'title' => 'Leads',
            'icon' => 'feather-users',

            'children' => [

                [
                    'title' => 'Modern Leads',
                    'icon' => 'feather-user',
                    'route' => 'modern.leads.index',
                ],

                [
                    'title' => 'All Leads',
                    'icon' => 'feather-list',
                    'route' => 'lead.index',
                ],

                [
                    'title' => 'Applications',
                    'icon' => 'feather-file-text',
                    'route' => 'lead.application',
                ],

                [
                    'title' => 'Lead Activity',
                    'icon' => 'feather-activity',
                    'route' => 'lead.leadActivity',
                ],

                [
                    'title' => 'Follow Ups',
                    'icon' => 'feather-phone-call',
                    'route' => 'lead.followUpData',
                ],

                [
                    'title' => 'Daily Report',
                    'icon' => 'feather-bar-chart-2',
                    'route' => 'lead.dailyReport',
                ],

                [
                    'title' => 'New Daily Report',
                    'icon' => 'feather-pie-chart',
                    'route' => 'lead.newdailyReport',
                ],

                [
                    'title' => 'Counsellor Report',
                    'icon' => 'feather-users',
                    'route' => 'lead.councillorReport',
                ],

                [
                    'title' => 'Campaign Performance',
                    'icon' => 'feather-trending-up',
                    'route' => 'lead.campaignPerformance',
                ],

                [
                    'title' => 'Source Performance',
                    'icon' => 'feather-layers',
                    'route' => 'lead.sourcePerformance',
                ],

            ]

        ],

    
        /*
        |--------------------------------------------------------------------------
        | CRM SETTINGS
        |--------------------------------------------------------------------------
        */

        [
            'title' => 'CRM Settings',
            'icon' => 'feather-settings',

            'children' => [

                [
                    'title' => 'Buckets',
                    'icon' => 'feather-folder',
                    'route' => 'bucket.index',
                ],

                [
                    'title' => 'Lead Questions',
                    'icon' => 'feather-help-circle',
                    'route' => 'lead_questions.index',
                ],

                [
                    'title' => 'Lead Sources',
                    'icon' => 'feather-database',
                    'route' => 'lead_sources.index',
                ],

                [
                    'title' => 'Categories',
                    'icon' => 'feather-grid',
                    'route' => 'category.index',
                ],

            ]

        ],

       
    ]

];