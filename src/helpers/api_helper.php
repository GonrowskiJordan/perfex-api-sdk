<?php

if (!function_exists('get_available_api_permissions')) {
    function get_available_api_permissions($data = [])
    {
        $viewGlobalName = _l('permission_view') . '(' . _l('permission_global') . ')';

        $firstPermissionsArray = [
            'get'           => _l('permission_get'),
            'search_get'    => _l('permission_search'),
            'post'          => _l('permission_create'),
            'delete'        => _l('permission_delete'),
            'put'           => _l('permission_update'),
        ];
        $secondPermissionsArray = [
            'get'           => _l('permission_list'),
            'search_get'    => _l('permission_search'),
        ];
        $secondonePermissionsArray = [
            'get'           => _l('permission_list'),
            'search_get'    => _l('permission_search'),
            'post'          => _l('permission_create'),
        ];
        $thirdPermissionsArray = [
            'get'           => _l('permission_list'),
            'post'          => _l('permission_create'),
            'delete'        => _l('permission_delete'),
        ];
        $forthPermissionsArray = [
            'get'           => _l('permission_get'),
        ];
        $fifthPermissionsArray = [
            'get'           => _l('permission_get'),
            'post'          => _l('permission_create'),
            'delete'        => _l('permission_delete'),
            'get_value'     => _l('permission_get_value'),
            'search_get'    => _l('permission_search'),
            'put'           => _l('permission_update'),
        ];
        $sixthPermissionsArray = [
            'get'           => _l('permission_get'),
        ];
        $seventhPermissionsArray = [
            'expense_category' => _l('expense_categories'),
            'payment_mode' => _l('payment_mode'),
            'tax_data' => _l('tax_data'),
        ];

        $apiPermissions = [
            'customers' => [
                'name'         => _l('clients'),
                'capabilities' => $firstPermissionsArray,
            ],
            'contacts' => [
                'name'         => _l('contacts'),
                'capabilities' => $firstPermissionsArray,
            ],
            'invoices' => [
                'name'         => _l('invoices'),
                'capabilities' => $firstPermissionsArray,
            ],
            'items' => [
                'name'         => _l('items'),
                'capabilities' => $secondPermissionsArray,
            ],
            'leads' => [
                'name'         => _l('leads'),
                'capabilities' => $firstPermissionsArray,
            ],
            'milestones' => [
                'name'         => _l('milestones'),
                'capabilities' => $firstPermissionsArray,
            ],
            'projects' => [
                'name'         => _l('projects'),
                'capabilities' => $firstPermissionsArray,
            ],
            'staffs' => [
                'name'         => _l('staffs'),
                'capabilities' => $firstPermissionsArray,
            ],
            'tasks' => [
                'name'         => _l('tasks'),
                'capabilities' => $firstPermissionsArray,
            ],
            'tickets' => [
                'name'         => _l('tickets'),
                'capabilities' => $firstPermissionsArray,
            ],
            'contracts' => [
                'name'         => _l('contracts'),
                'capabilities' => $firstPermissionsArray,
            ],
            'credit_notes' => [
                'name'         => _l('credit_notes'),
                'capabilities' => $firstPermissionsArray,
            ],
            'custom_fields' => [
                'name'         => _l('custom_fields'),
                'capabilities' => $firstPermissionsArray,
            ],
            'estimates' => [
                'name'         => _l('estimates'),
                'capabilities' => $firstPermissionsArray,
            ],
            'common' => [
                'name'         => _l('common'),
                'capabilities' => $seventhPermissionsArray,
            ],
            'expenses' => [
                'name'         => _l('expenses'),
                'capabilities' => $firstPermissionsArray,
            ],
            'taxes' => [
                'name'         => _l('taxes'),
                'capabilities' => $forthPermissionsArray,
            ],
            'payment_methods' => [
                'name'         => _l('payment_methods'),
                'capabilities' => $forthPermissionsArray,
            ],
            'payments' => [
                'name'         => _l('payments'),
                'capabilities' => $secondonePermissionsArray,
            ],
            'proposals' => [
                'name'         => _l('proposals'),
                'capabilities' => $firstPermissionsArray,
            ],
            'calendar' => [
                'name'         => _l('calendar'),
                'capabilities' => $firstPermissionsArray,
            ],
            'subscriptions' => [
                'name'         => _l('subscriptions'),
                'capabilities' => $firstPermissionsArray,
            ],
            'timesheets' => [
                'name'         => _l('timesheets'),
                'capabilities' => $firstPermissionsArray,
            ],
        ];

        return hooks()->apply_filters('api_permissions', $apiPermissions, $data);
    }
}