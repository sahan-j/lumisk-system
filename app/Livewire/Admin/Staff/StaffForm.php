<?php

namespace App\Livewire\Admin\Staff;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Staff Member')]
class StaffForm extends Component
{
    public ?User $user = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $phone = null;
    public ?string $job_title = null;
    public string $role = 'staff';
    public bool $is_active = true;

    /** Effective permission state keyed by sanitized permission name (dots → underscores). */
    public array $permState = [];

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            abort_if($user->isSuperAdmin() && ! Auth::user()->isSuperAdmin(), 403);

            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone;
            $this->job_title = $user->job_title;
            $this->role = $user->role === 'super_admin' ? 'super_admin' : $user->role;
            $this->is_active = (bool) $user->is_active;
            $this->permState = $this->stateFromPermissions($user->effectivePermissions());
        } else {
            $this->permState = $this->stateFromPermissions($this->roleDefaults('staff'));
        }
    }

    /** When the role changes, reset the override grid to that role's defaults. */
    public function updatedRole(string $value): void
    {
        $this->permState = $this->stateFromPermissions($this->roleDefaults($value));
    }

    protected function keyFor(string $name): string
    {
        return str_replace('.', '_', $name);
    }

    /** @return array<int, string> */
    protected function roleDefaults(string $role): array
    {
        return DB::table('role_permissions')->where('role', $role)->pluck('permission_name')->all();
    }

    /** @return array<string, bool> */
    protected function stateFromPermissions(array $names): array
    {
        $state = [];
        foreach (Permission::all() as $perm) {
            $state[$this->keyFor($perm->name)] = in_array($perm->name, $names, true);
        }

        return $state;
    }

    public function save()
    {
        abort_unless(Auth::user()->hasPermission($this->user ? 'staff.edit' : 'staff.create'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'password' => [$this->user ? 'nullable' : 'required', 'nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'in:admin,staff'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'role' => $validated['role'],
            'is_active' => $this->is_active,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user) {
            $this->user->update($data);
            $user = $this->user;
            $message = 'Staff member updated!';
        } else {
            $data['password'] = Hash::make($this->password);
            $data['created_by'] = Auth::id();
            $user = User::create($data);
            $message = 'Staff member created!';
        }

        $this->syncOverrides($user);

        $this->dispatch('toast', type: 'success', message: $message);

        return $this->redirect(route('admin.staff.index'), navigate: true);
    }

    /** Persist only the permissions that differ from the selected role's defaults. */
    protected function syncOverrides(User $user): void
    {
        $defaults = $this->roleDefaults($user->role);
        $rows = [];

        foreach (Permission::all() as $perm) {
            $checked = (bool) ($this->permState[$this->keyFor($perm->name)] ?? false);
            $isDefault = in_array($perm->name, $defaults, true);

            if ($checked !== $isDefault) {
                $rows[] = ['user_id' => $user->id, 'permission_name' => $perm->name, 'granted' => $checked];
            }
        }

        DB::table('user_permissions')->where('user_id', $user->id)->delete();
        if ($rows) {
            DB::table('user_permissions')->insert($rows);
        }
    }

    public function render()
    {
        $permissionGroups = Permission::orderBy('id')->get()->groupBy('group');
        $roleDefaults = $this->roleDefaults($this->role === 'super_admin' ? 'super_admin' : $this->role);

        return view('livewire.admin.staff.staff-form', [
            'permissionGroups' => $permissionGroups,
            'roleDefaults' => $roleDefaults,
        ]);
    }
}
