<?php

return [
    'brand' => [
        'name' => 'Pult',
        'sub' => 'Holding control panel',
    ],

    'sidebar' => [
        'section' => [
            'data' => 'Data',
            'companies' => 'Units',
        ],
    ],

    'nav' => [
        'dashboard' => 'Dashboard',
        'personnel' => 'Personnel',
        'strategy' => 'Strategy & Vision',
        'ideas' => 'Ideas & Insights',
        'agreements' => 'Agreements & Docs',
        'responsibilities' => 'Responsibilities',
        'sla' => 'SLA',
        'services' => 'Services',
        'reports' => 'Reports',
        'risks' => 'Risks, Issues, Workarounds',
        'processes' => 'Processes',
    ],

    'home' => [
        'greeting' => 'Welcome to Pult',
        'sub' => 'Holding control panel: quick actions and overview',
        'tickets_label' => 'Quick requests',
    ],

    'ticket' => [
        'cta' => 'Create request',
        'dayoff' => [
            'title' => 'Day off / PTO',
            'desc' => 'Request time away from work',
        ],
        'server' => [
            'title' => 'Server / resources',
            'desc' => 'Request infrastructure capacity',
        ],
        'domain' => [
            'title' => 'Domain',
            'desc' => 'Register or renew a domain',
        ],
        'payment' => [
            'title' => 'Payment',
            'desc' => 'Finance request',
        ],
    ],

    'personnel' => [
        'title' => 'Personnel',
        'subtitle' => 'All holding employees: :count people',
        'add' => 'Add employee',
        'edit' => 'Edit employee',
        'delete_confirm' => 'Delete this employee?',
        'flash' => [
            'created' => 'Employee added',
            'updated' => 'Employee updated',
            'deleted' => 'Employee deleted',
        ],
    ],

    'table' => [
        'name' => 'Name',
        'position' => 'Position',
        'company' => 'Company',
        'department' => 'Department',
        'email' => 'Email',
        'telegram' => 'Telegram',
        'status' => 'Status',
        'actions' => 'Actions',
        'vacancy' => '[Vacancy]',
        'empty' => 'No employees yet',
    ],

    'status' => [
        'active' => 'Active',
        'vacancy' => 'Vacancy',
    ],

    'modal' => [
        'field' => [
            'name' => 'Name',
            'name_ph' => 'John Doe',
            'position' => 'Position',
            'position_ph' => 'CEO',
            'status' => 'Status',
            'company' => 'Company',
            'department' => 'Department',
            'email' => 'Email',
            'email_ph' => 'john@company.com',
            'telegram' => 'Telegram',
            'telegram_ph' => '@username',
        ],
        'btn' => [
            'cancel' => 'Cancel',
            'save' => 'Save',
            'delete' => 'Delete',
        ],
    ],

    'placeholder' => [
        'wip' => 'Section under development',
    ],

    'ideas' => [
        'title' => 'Ideas & Insights',
        'subtitle' => "Oleksiy's ideas as a visionary — for consideration by company leaders",
        'empty' => 'No ideas yet',
        'btn' => [
            'add' => 'Add idea',
            'edit' => 'Edit',
            'delete' => 'Delete idea',
            'back' => 'Back to list',
        ],
        'col' => [
            'id' => 'ID',
            'company' => 'Company',
            'idea' => 'Idea',
            'status' => 'Status',
            'author' => 'Author',
            'priority' => 'Priority',
            'created' => 'Created',
        ],
        'status' => [
            'new' => 'New',
            'under_review' => 'Under review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'in_progress' => 'In progress',
            'done' => 'Done',
        ],
        'priority' => [
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
        ],
        'filter' => [
            'btn' => 'Filter',
            'add' => 'Add filter',
            'clear' => 'Clear all',
            'op' => [
                'is' => '=',
                'is_not' => '≠',
                'contains' => 'contains',
            ],
        ],
        'detail' => [
            'desc' => 'Description',
            'impact' => 'Why is this idea important and what is the expected impact?',
        ],
        'form' => [
            'title' => 'Idea title',
            'title_ph' => 'Short summary',
            'description' => 'Description',
            'description_ph' => 'What exactly is being proposed',
            'impact' => 'Expected impact',
            'impact_ph' => 'Why it matters and what is the benefit',
            'unit' => 'Company',
            'author' => 'Author',
            'priority' => 'Priority',
            'status' => 'Status',
        ],
        'flash' => [
            'created' => 'Idea added',
            'updated' => 'Idea updated',
            'deleted' => 'Idea deleted',
        ],
        'delete_confirm' => 'Delete this idea?',
    ],

    'risks' => [
        'title' => 'Risks, Issues, Workarounds',
        'subtitle' => 'Log of risks, incidents, fuck-ups and workarounds',
        'empty' => 'No entries',
        'delete_confirm' => 'Delete this entry?',
        'btn' => [
            'add' => 'Add',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'back' => 'Back to list',
        ],
        'log' => [
            'risk' => ['title' => 'Risk Log'],
            'issue' => ['title' => 'Issue Log'],
            'fuckup' => ['title' => 'Fuck-up Log'],
            'workaround' => ['title' => 'Workarounds Log'],
        ],
        'col' => [
            'id' => 'ID',
            'name' => 'Name',
            'date' => 'Date',
            'owner' => 'Owner',
            'status' => 'Status',
        ],
        'status' => [
            'open' => 'Open',
            'in_progress' => 'In progress',
            'mitigated' => 'Mitigated',
            'closed' => 'Closed',
            'active' => 'Active',
            'resolved' => 'Resolved',
        ],
        'detail' => [
            'desc' => 'Description',
        ],
        'field' => [
            'type' => 'Entry type',
        ],
        'flash' => [
            'created' => 'Entry added',
            'updated' => 'Entry updated',
            'deleted' => 'Entry deleted',
        ],
    ],

    'services' => [
        'title' => 'Holding services',
        'subtitle' => 'Total: :total · Active: :active · Subscriptions: :subs',
        'empty' => 'No services found',
        'free' => 'Free',
        'monthly_total' => 'Monthly spend',
        'delete_confirm' => 'Delete this service?',
        'btn' => [
            'add' => 'Add service',
            'edit' => 'Edit service',
        ],
        'filter' => [
            'all' => 'All',
            'active' => 'Active',
            'subscription' => 'Subscriptions',
            'once' => 'One-time',
        ],
        'col' => [
            'name' => 'Service',
            'category' => 'Category',
            'company' => 'Company',
            'cost' => 'Cost',
            'billing' => 'Period',
            'next_payment' => 'Next payment',
            'status' => 'Status',
        ],
        'billing' => [
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'once' => 'One-time',
        ],
        'status' => [
            'active' => 'Active',
            'trial' => 'Trial',
            'inactive' => 'Inactive',
        ],
        'field' => [
            'name' => 'Name',
            'url' => 'Website',
            'category' => 'Category',
            'company' => 'Company',
            'cost' => 'Cost',
            'currency' => 'Currency',
            'billing' => 'Billing',
            'next_payment' => 'Next payment',
            'status' => 'Status',
        ],
        'flash' => [
            'created' => 'Service added',
            'updated' => 'Service updated',
            'deleted' => 'Service deleted',
        ],
    ],
];
