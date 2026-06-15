<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'dashboard.view', 'label' => 'View Dashboard', 'group' => 'Dashboard'],

            ['name' => 'clients.view', 'label' => 'View Clients', 'group' => 'Clients'],
            ['name' => 'clients.create', 'label' => 'Create Clients', 'group' => 'Clients'],
            ['name' => 'clients.edit', 'label' => 'Edit Clients', 'group' => 'Clients'],
            ['name' => 'clients.delete', 'label' => 'Delete Clients', 'group' => 'Clients'],

            ['name' => 'invoices.view', 'label' => 'View Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.create', 'label' => 'Create Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.edit', 'label' => 'Edit Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.delete', 'label' => 'Delete Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.send', 'label' => 'Send Invoices', 'group' => 'Invoices'],
            ['name' => 'invoices.mark_paid', 'label' => 'Mark as Paid', 'group' => 'Invoices'],

            ['name' => 'estimates.view', 'label' => 'View Estimates', 'group' => 'Estimates'],
            ['name' => 'estimates.create', 'label' => 'Create Estimates', 'group' => 'Estimates'],
            ['name' => 'estimates.edit', 'label' => 'Edit Estimates', 'group' => 'Estimates'],
            ['name' => 'estimates.delete', 'label' => 'Delete Estimates', 'group' => 'Estimates'],
            ['name' => 'estimates.send', 'label' => 'Send Estimates', 'group' => 'Estimates'],

            ['name' => 'payments.view', 'label' => 'View Payments', 'group' => 'Payments'],
            ['name' => 'payments.record', 'label' => 'Record Payments', 'group' => 'Payments'],
            ['name' => 'payments.delete', 'label' => 'Delete Payments', 'group' => 'Payments'],

            ['name' => 'projects.view', 'label' => 'View Projects', 'group' => 'Projects'],
            ['name' => 'projects.create', 'label' => 'Create Projects', 'group' => 'Projects'],
            ['name' => 'projects.edit', 'label' => 'Edit Projects', 'group' => 'Projects'],
            ['name' => 'projects.delete', 'label' => 'Delete Projects', 'group' => 'Projects'],

            ['name' => 'tickets.view', 'label' => 'View Tickets', 'group' => 'Tickets'],
            ['name' => 'tickets.reply', 'label' => 'Reply to Tickets', 'group' => 'Tickets'],
            ['name' => 'tickets.manage', 'label' => 'Manage Ticket Status', 'group' => 'Tickets'],

            ['name' => 'expenses.view', 'label' => 'View Expenses', 'group' => 'Expenses'],
            ['name' => 'expenses.create', 'label' => 'Create Expenses', 'group' => 'Expenses'],
            ['name' => 'expenses.edit', 'label' => 'Edit Expenses', 'group' => 'Expenses'],
            ['name' => 'expenses.delete', 'label' => 'Delete Expenses', 'group' => 'Expenses'],

            ['name' => 'reports.view', 'label' => 'View Reports', 'group' => 'Reports'],
            ['name' => 'reports.export', 'label' => 'Export Reports', 'group' => 'Reports'],

            ['name' => 'settings.view', 'label' => 'View Settings', 'group' => 'Settings'],
            ['name' => 'settings.edit', 'label' => 'Edit Settings', 'group' => 'Settings'],

            ['name' => 'staff.view', 'label' => 'View Staff', 'group' => 'Staff'],
            ['name' => 'staff.create', 'label' => 'Create Staff', 'group' => 'Staff'],
            ['name' => 'staff.edit', 'label' => 'Edit Staff', 'group' => 'Staff'],
            ['name' => 'staff.delete', 'label' => 'Delete Staff', 'group' => 'Staff'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        $all = collect($permissions)->pluck('name')->all();

        // Admin: everything except staff create/delete and settings.edit.
        $admin = array_values(array_diff($all, ['staff.create', 'staff.delete', 'settings.edit']));

        $staff = [
            'dashboard.view',
            'clients.view',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'estimates.view', 'estimates.create', 'estimates.edit',
            'payments.view', 'payments.record',
            'projects.view',
            'tickets.view', 'tickets.reply',
            'expenses.view', 'expenses.create',
        ];

        $this->syncRole('super_admin', $all);
        $this->syncRole('admin', $admin);
        $this->syncRole('staff', $staff);
    }

    private function syncRole(string $role, array $permissions): void
    {
        DB::table('role_permissions')->where('role', $role)->delete();
        DB::table('role_permissions')->insert(
            collect($permissions)->map(fn ($name) => ['role' => $role, 'permission_name' => $name])->all()
        );
    }
}
