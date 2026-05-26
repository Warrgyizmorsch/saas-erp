<?php

// Retrieve authenticated user's role
$roleId = null;
if (function_exists('auth') && auth()->check()) {
    $roleId = auth()->user()->role_id;
}

// Full list of all menus with target role mappings
$allItems = [
    // 1. Dashboard
    [
        'title' => 'Inventory Dashboard',
        'icon' => 'feather-grid',
        'route' => '/inventory',
        'roles' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    ],

    // 3. Inventories
    [
        'title' => 'Inventories',
        'icon' => 'feather-package',
        'roles' => [1, 2, 4, 5, 9, 10],
        'children' => [
            [
                'title' => 'Units',
                'route' => '/inventory/units',
            ],
            [
                'title' => 'Machine',
                'route' => '/inventory/product',
            ],
            [
                'title' => 'Project',
                'route' => '/inventory/project',
            ],
        ]
    ],

    // 4. Request Slip
    [
        'title' => 'Request Slip',
        'icon' => 'feather-file-text',
        'roles' => [1, 2, 5, 6, 7, 9],
        'children' => [
            [
                'title' => 'Create RS',
                'route' => '/inventory/request-slip/create',
            ],
            [
                'title' => 'My Rs',
                'route' => '/inventory/request-slip',
            ],
            [
                'title' => 'Semi Finish Goods Raw',
                'route' => '/inventory/required-vs-available',
            ],
            [
                'title' => 'Approval',
                'route' => '/inventory/approval/requisition',
            ],
            [
                'title' => 'Requisitions',
                'route' => '/inventory/approval/requisition',
            ],
        ]
    ],

    // 5. Employee Module
    [
        'title' => 'Employee Module',
        'icon' => 'feather-users',
        'roles' => [1, 2, 8],
        'children' => [
            [
                'title' => 'Employee Dashboard',
                'route' => '/inventory/employee-dashboard',
            ]
        ]
    ],

    // 6. Issue Slips
    [
        'title' => 'Issue Slips',
        'icon' => 'feather-share-2',
        'roles' => [1, 2, 5],
        'children' => [
            [
                'title' => 'Create Issue',
                'route' => '/inventory/issue/create',
            ],
            [
                'title' => 'Deparments',
                'route' => '/inventory/departments',
            ],
            [
                'title' => 'Suppliers',
                'route' => '/inventory/suppliers',
            ],
            [
                'title' => 'Issued List',
                'route' => '/inventory/issue/view-list',
            ],
            [
                'title' => 'Inventory categories',
                'route' => '/inventory/categories',
            ],
            [
                'title' => 'Opening Stock',
                'route' => '/inventory/inventory/opening-stock',
            ],
        ]
    ],

    // 7. Purchase Request
    [
        'title' => 'Purchase Request',
        'icon' => 'feather-shopping-cart',
        'roles' => [1, 2, 4, 10],
        'children' => [
            [
                'title' => 'Add',
                'route' => '/inventory/purchase_request',
            ],
            [
                'title' => 'View List',
                'route' => '/inventory/purchase_request/list-view',
            ],
        ]
    ],

    // 8. Job Card
    [
        'title' => 'Job Card',
        'icon' => 'feather-clipboard',
        'roles' => [1, 2, 3, 4, 10],
        'children' => [
            [
                'title' => 'Create',
                'route' => '/inventory/job_card/create',
            ],
            [
                'title' => 'view',
                'route' => '/inventory/job_card/view',
            ],
            [
                'title' => 'PR Approval',
                'route' => '/inventory/purchase_request/approval-view',
            ],
            [
                'title' => 'Required V/S Available',
                'route' => '/inventory/required-vs-available',
            ],
            [
                'title' => 'Categories',
                'route' => '/inventory/categories',
            ],
            [
                'title' => 'Job Card Vendors',
                'route' => '/inventory/vendor',
            ],
        ]
    ],

    // 9. GRN
    [
        'title' => 'GRN',
        'icon' => 'feather-check-square',
        'roles' => [1, 2, 3, 4, 10],
        'children' => [
            [
                'title' => 'Create GRN',
                'route' => '/inventory/grn/create',
            ],
            [
                'title' => 'GRN List',
                'route' => '/inventory/grn/list',
            ],
        ]
    ],

    // 10. Purchase Order
    [
        'title' => 'Purchase Order',
        'icon' => 'feather-file-minus',
        'roles' => [1, 2, 3, 4, 10],
        'children' => [
            [
                'title' => 'Pending PO',
                'route' => '/inventory/purchase-order/approval-view',
            ],
            [
                'title' => 'Create PO',
                'route' => '/inventory/purchase-order/create',
            ],
            [
                'title' => 'GRN List',
                'route' => '/inventory/grn/list',
            ],
            [
                'title' => 'Exceed RS',
                'route' => '/inventory/approval/admin',
            ],
            [
                'title' => 'PO Approval',
                'route' => '/inventory/purchase-order/approval-view',
            ],
            [
                'title' => 'All PO',
                'route' => '/inventory/purchase-order',
            ],
        ]
    ],

    // 11. Request Slip Office
    [
        'title' => 'Request Slip Office',
        'icon' => 'feather-briefcase',
        'roles' => [1, 2, 5, 6, 7, 9],
        'children' => [
            [
                'title' => 'Placement',
                'route' => '/inventory/placement',
            ],
            [
                'title' => 'View All Rs',
                'route' => '/inventory/request-slip/view-all',
            ],
            [
                'title' => 'Current Stock',
                'route' => '/inventory/current-stock',
            ],
            [
                'title' => 'Create Safety RS',
                'route' => '/inventory/safety-create',
            ],
            [
                'title' => 'Create Office RS',
                'route' => '/inventory/safety-create',
            ],
        ]
    ],

    // 12. Employee Dashboard
    [
        'title' => 'Employee Dashboard',
        'icon' => 'feather-user',
        'route' => '/inventory/employee-dashboard',
        'roles' => [1, 2, 8],
    ],

    // 13. Consumption
    [
        'title' => 'Consumption',
        'icon' => 'feather-activity',
        'roles' => [1, 2, 3, 5, 6],
        'children' => [
            [
                'title' => 'consumption List',
                'route' => '/inventory/consumption/list',
            ],
        ]
    ],
];

// Filter items based on the user's role ID (1 to 10)
$filteredItems = [];
foreach ($allItems as $item) {
    if (isset($item['roles']) && is_array($item['roles'])) {
        if ($roleId !== null && in_array($roleId, $item['roles'])) {
            $filteredItem = $item;
            unset($filteredItem['roles']);

            if (isset($filteredItem['children'])) {
                foreach ($filteredItem['children'] as $idx => $child) {
                    if (isset($child['roles']) && is_array($child['roles'])) {
                        if (!in_array($roleId, $child['roles'])) {
                            unset($filteredItem['children'][$idx]);
                        } else {
                            unset($filteredItem['children'][$idx]['roles']);
                        }
                    }
                }
                $filteredItem['children'] = array_values($filteredItem['children']);
            }
            $filteredItems[] = $filteredItem;
        }
    } else {
        $filteredItems[] = $item;
    }
}

return [
    'name' => 'Inventory',
    'icon' => '📦',
    'items' => $filteredItems,
];