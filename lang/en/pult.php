<?php

return [
    'brand' => [
        'name' => 'Pult',
        'sub' => 'Holding control panel',
        'tagline' => 'Single control point for the holding: people, ideas, risks, services — all in one interface.',
    ],

    'auth' => [
        'common' => [
            'email' => 'Email',
            'email_ph' => 'you@company.com',
            'password' => 'Password',
            'password_ph' => '••••••••',
            'confirm_password' => 'Confirm password',
            'name' => 'Name',
            'name_ph' => 'John Doe',
            'remember_me' => 'Remember me',
        ],
        'login' => [
            'title' => 'Sign in to Pult',
            'sub' => 'Log in to continue managing the holding',
            'submit' => 'Sign in',
            'forgot_link' => 'Forgot your password?',
            'no_account' => "Don't have an account?",
            'register_link' => 'Create account',
        ],
        'register' => [
            'title' => 'Create account',
            'sub' => 'Fill out the form to gain access to Pult',
            'submit' => 'Register',
            'have_account' => 'Already have an account?',
            'login_link' => 'Sign in',
        ],
        'forgot' => [
            'title' => 'Reset password',
            'sub' => "Enter your email — we'll send you a reset link.",
            'submit' => 'Send reset link',
            'back_to_login' => 'Back to sign in',
        ],
        'reset' => [
            'title' => 'New password',
            'sub' => 'Set a new password for your account.',
            'submit' => 'Save password',
        ],
        'confirm' => [
            'title' => 'Confirm password',
            'sub' => 'This is a protected area — confirm your password to continue.',
            'submit' => 'Confirm',
        ],
        'verify' => [
            'title' => 'Verify email',
            'sub' => 'We sent you a verification link. Please check your inbox.',
            'sent' => 'A fresh verification link has been sent to your email.',
            'resend' => 'Resend email',
            'logout' => 'Log out',
        ],
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
        'hiring' => 'Hiring',
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

    'unit_dashboard' => [
        'sub' => 'Company data and metrics',
        'employees_title' => 'Employees',
        'ideas_title' => 'Recent ideas',
        'services_title' => 'Company services',
        'view_all' => 'All →',
        'no_data' => 'No data',
    ],

    'dashboard' => [
        'title' => 'Dashboard',
        'sub' => 'Key metrics and holding status at a glance',
        'section' => [
            'overview' => 'Overview',
            'activity' => 'Recent activity',
            'tickets' => 'Quick requests',
        ],
        'personnel' => ['title' => 'Personnel', 'active' => 'Active', 'vacancies' => 'Vacancies'],
        'ideas' => ['title' => 'Ideas', 'new' => 'New', 'in_progress' => 'In progress', 'approved' => 'Approved'],
        'risks' => ['title' => 'Risks', 'open' => 'Open', 'total' => 'Total entries'],
        'services' => ['title' => 'Services', 'active' => 'Active', 'mrr' => 'MRR'],
        'activity_empty' => 'No activity yet',
    ],

    'ticket' => [
        'cta' => 'Create request',
        'submit' => 'Submit request',
        'dayoff' => [
            'title' => 'Request day off',
            'desc' => 'Request a day off or PTO. Specify dates and your backup.',
        ],
        'server' => [
            'title' => 'Request server',
            'desc' => 'Request a new server or cloud resources for a project.',
        ],
        'domain' => [
            'title' => 'Request domain',
            'desc' => 'Register or renew a domain name for the company.',
        ],
        'payment' => [
            'title' => 'Request payment',
            'desc' => 'Initiate a payment, invoice or financial operation.',
        ],
        'field' => [
            'date_from' => 'Start date',
            'date_to' => 'End date',
            'reason' => 'Reason',
            'reason_ph' => 'Personal reasons',
            'backup' => 'Backup person',
            'backup_ph' => "Colleague's name",
            'project' => 'Project',
            'server_type' => 'Server type',
            'config' => 'Configuration',
            'config_ph' => '4 vCPU, 8GB RAM, 100GB SSD',
            'deadline' => 'Needed by',
            'domain_name' => 'Domain name',
            'domain_name_ph' => 'myproject',
            'tld' => 'TLD',
            'for_project' => 'For project',
            'for_project_ph' => 'PlayDuck',
            'period' => 'Registration period',
            'amount' => 'Amount',
            'amount_ph' => '100',
            'currency' => 'Currency',
            'recipient' => 'Recipient',
            'recipient_ph' => 'Company LLC',
            'purpose' => 'Payment purpose',
            'purpose_ph' => 'What is this payment for',
            'invoice' => 'Invoice link / file',
            'invoice_ph' => 'https://...',
        ],
        'period' => [
            '1y' => '1 year',
            '2y' => '2 years',
            '3y' => '3 years',
        ],
        'success' => [
            'title' => 'Request submitted!',
            'sub' => 'The responsible person will be notified and will get back to you.',
            'close' => 'Close',
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
        'manager' => 'Manager',
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
        'fired' => 'Fired',
    ],

    'personnel_tabs' => [
        'active' => 'Current staff',
        'vacancy' => 'Vacancies',
        'fired' => 'Former employees',
    ],

    'work_stage' => [
        'onboarding' => 'Onboarding',
        'probation' => 'Probation',
        'employee' => 'Employee',
        'offboarding' => 'Offboarding',
        'label' => 'Stage',
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
        'wip' => 'Section under development — coming in a later phase',
    ],

    'page' => [
        'strategy' => [
            'title' => 'Strategy & Vision',
            'sub' => 'Holding-wide goals and strategic directions',
        ],
        'agreements' => [
            'title' => 'Agreements & Documents',
            'sub' => 'Internal agreements, regulations and key documents',
        ],
        'responsibilities' => [
            'title' => 'Responsibilities',
            'sub' => 'Who owns what: expectations, boundaries, escalation',
        ],
        'sla' => [
            'title' => 'SLA',
            'sub' => 'Service-level agreements between holding companies',
        ],
        'reports' => [
            'title' => 'Reports',
            'sub' => 'Regular reporting and per-company dashboards',
        ],
        'processes' => [
            'title' => 'Processes & Protocols',
            'sub' => 'Standard operating procedures and communication protocols',
        ],
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

    'footer' => [
        'info' => 'Info',
        'codex' => 'Codex',
        'dictionary' => 'Dictionary',
    ],

    'codex' => [
        'title' => 'Holding Codex',
        'subtitle' => 'Principles, standards and rules',
        'intro' => 'The Codex defines unified standards of work, behavior and decision-making across all holding companies. Compliance is mandatory for all participants.',
        'section' => [
            'mission' => 'Mission & Goals',
            'values' => 'Values',
            'conduct' => 'Ethics & Conduct',
            'standards' => 'Work Standards',
        ],
        'item' => [
            'mission' => [
                'Build products and services that solve real customer problems',
                'Ensure long-term growth and sustainability of each holding company',
                'Foster a culture of accountability and continuous improvement',
            ],
            'values' => [
                'Honesty and transparency in all communications and decisions',
                'Accountability for outcomes, not processes',
                "Respect for time and resources: your own, your colleagues', and your clients'",
            ],
            'conduct' => [
                'Constructive feedback is the foundation of team growth',
                'Conflicts are resolved through dialogue, not hierarchy',
                'Discrimination, pressure and manipulation are not tolerated',
            ],
            'standards' => [
                'Agreements are recorded in writing and delivered on time',
                'Metrics and decisions are transparent across the holding',
                'Documentation is maintained on a single platform in real time',
            ],
        ],
    ],

    'dictionary' => [
        'title' => 'Holding Dictionary',
        'subtitle' => 'Term base: :count definitions',
        'search_ph' => 'Search term...',
        'empty' => 'Term not found',
        'terms' => [
            ['term' => 'Holding', 'def' => 'A group of legally independent companies under unified management and strategy.'],
            ['term' => 'Business Unit (BU)', 'def' => 'A separate company or division of the holding with its own P&L and team.'],
            ['term' => 'KPI', 'def' => 'Key Performance Indicators — measurable metrics of goal achievement.'],
            ['term' => 'OKR', 'def' => 'Objectives & Key Results — a methodology for setting and tracking goals.'],
            ['term' => 'Pipeline', 'def' => 'A funnel of deals or tasks reflecting stages of progress towards a result.'],
            ['term' => 'Roadmap', 'def' => 'A visual plan for product or project development with time markers.'],
            ['term' => 'Stakeholder', 'def' => 'An interested party: anyone who affects a project or is affected by it.'],
            ['term' => 'Sprint', 'def' => 'A fixed work cycle (usually 1-2 weeks) with a clear set of tasks.'],
            ['term' => 'Retrospective', 'def' => 'A regular team meeting to analyze the past period and find improvements.'],
            ['term' => 'MRR', 'def' => 'Monthly Recurring Revenue — monthly recurring income of a subscription business.'],
        ],
    ],

    'info' => [
        'title' => 'Info',
        'hero_sub' => 'Corporate ERM panel for the holding',
        'intro' => [
            'Pult is the single control point for the holding: employees, services, agreements, processes and quick requests — all in one interface.',
            'Navigate via the left sidebar. Switch the interface language in the footer (RU / UK / EN). Click the Pult logo to return to the home page.',
        ],
        'goals' => [
            'label' => 'Pult Goals',
            'items' => [
                ['title' => 'Single Operating Contour', 'desc' => 'Work inside Pult, within one space — no switching between systems. Earn from holding companies and manage from one place.'],
                ['title' => 'Track Everything', 'desc' => 'Employees, services, agreements, risks, expenses and decisions — recorded in Pult. Nothing gets lost.'],
                ['title' => 'Zero Noise', 'desc' => 'Fewer chats, fewer "where was that?". Everything lives in Pult — not in someone\'s head or a messenger.'],
                ['title' => 'Control Made Easy', 'desc' => 'No need to keep everything in your head or collect statuses manually. Everything is visible at once.'],
            ],
        ],
        'features' => [
            'title' => "What's in Pult",
            'items' => [
                ['title' => 'Home', 'desc' => 'Quick requests with one click: day-off, server, domain, payment.'],
                ['title' => 'Personnel', 'desc' => 'List of employees and vacancies across units with filters and CRUD.'],
                ['title' => 'Ideas & Insights', 'desc' => 'Journal of visionary ideas with Notion-style filters and detail view.'],
                ['title' => 'Risks', 'desc' => 'Four logs: Risk / Issue / Fuckup / Workaround.'],
                ['title' => 'Services', 'desc' => 'Subscriptions with monthly spend calculation and filters.'],
                ['title' => 'Codex & Dictionary', 'desc' => 'Unified principles and terminology of the holding.'],
                ['title' => 'Three languages', 'desc' => 'RU / UK / EN without page reload.'],
                ['title' => 'Audit log', 'desc' => 'All entity changes are written to activity_log via Spatie.'],
            ],
        ],
        'changelog' => [
            'title' => 'Phase history',
            'latest' => 'NEW',
            'entries' => [
                ['version' => 'Phase 3', 'title' => 'Risks & Services', 'items' => ['Risks: 4-log polymorphic table', 'Services: MRR calculator + filters', '18 new tests']],
                ['version' => 'Phase 2', 'title' => 'Ideas', 'items' => ['Notion-style filters', 'Detail page /ideas/ID-001', '15 tests']],
                ['version' => 'Phase 1', 'title' => 'Units + Personnel + i18n', 'items' => ['9 units, 11 employees', 'Pult Sidebar, 11 nav items', 'RU/UK/EN switcher']],
                ['version' => 'Phase 0', 'title' => 'Foundation', 'items' => ['Laravel 13 + Breeze Vue+TS', 'spatie/permission, activitylog, telescope', 'Tailwind 4, Larastan level 6']],
            ],
        ],
        'units' => [
            'label' => 'Holding Units',
            'revenue' => '$ — revenue-generating company',
            'service' => '🛠 — service company',
        ],
    ],
];
