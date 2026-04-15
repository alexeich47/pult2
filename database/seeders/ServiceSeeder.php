<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Ported 1:1 from Pult 1.2 legacy `js/pages/ServicesPage.js` services array.
     *
     * Two legacy typos are corrected during the port:
     *  - 'aptcatalog' → 'affcatalog'
     *  - 'playbook'   → 'playduck'
     *
     * Both referred to non-existent units in the legacy COMPANIES list.
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'AWS',              'url' => 'aws.amazon.com',       'category' => 'Хостинг',      'unit_id' => 'affcatalog', 'cost' => 150.00, 'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-01', 'status' => 'active'],
            ['name' => 'GitHub Teams',     'url' => 'github.com',           'category' => 'Инструменты',  'unit_id' => 'holding',    'cost' => 8.00,   'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-10', 'status' => 'active'],
            ['name' => 'Figma',            'url' => 'figma.com',            'category' => 'Дизайн',       'unit_id' => 'playduck',   'cost' => 45.00,  'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-15', 'status' => 'active'],
            ['name' => 'Notion',           'url' => 'notion.so',            'category' => 'Инструменты',  'unit_id' => 'holding',    'cost' => 16.00,  'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-03', 'status' => 'active'],
            ['name' => 'Google Workspace', 'url' => 'workspace.google.com', 'category' => 'Коммуникации', 'unit_id' => 'holding',    'cost' => 72.00,  'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-20', 'status' => 'active'],
            ['name' => 'Cloudflare',       'url' => 'cloudflare.com',       'category' => 'Безопасность', 'unit_id' => 'affcatalog', 'cost' => 20.00,  'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-08', 'status' => 'active'],
            ['name' => 'Vercel',           'url' => 'vercel.com',           'category' => 'Хостинг',      'unit_id' => 'playduck',   'cost' => 20.00,  'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-12', 'status' => 'active'],
            ['name' => '1Password',        'url' => '1password.com',        'category' => 'Безопасность', 'unit_id' => 'holding',    'cost' => 19.95,  'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-18', 'status' => 'active'],
            ['name' => 'Slack',            'url' => 'slack.com',            'category' => 'Коммуникации', 'unit_id' => 'holding',    'cost' => 0.00,   'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-30', 'status' => 'trial'],
            ['name' => 'Jira',             'url' => 'atlassian.com',        'category' => 'Управление',   'unit_id' => 'citadel',    'cost' => 8.15,   'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => '2025-05-25', 'status' => 'active'],
            ['name' => 'Grafana Cloud',    'url' => 'grafana.com',          'category' => 'Аналитика',    'unit_id' => 'affcatalog', 'cost' => 0.00,   'currency' => 'USD', 'billing' => 'monthly', 'next_payment' => null,         'status' => 'active'],
            ['name' => 'Namecheap',        'url' => 'namecheap.com',        'category' => 'Хостинг',      'unit_id' => 'holding',    'cost' => 120.00, 'currency' => 'USD', 'billing' => 'yearly',  'next_payment' => '2025-11-01', 'status' => 'active'],
        ];

        foreach ($rows as $row) {
            Service::create($row);
        }
    }
}
