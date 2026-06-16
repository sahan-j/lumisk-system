<?php

namespace App\Livewire\Admin\Staff;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Staff Members')]
class StaffIndex extends Component
{
    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function toggleActive(int $id): void
    {
        abort_unless(Auth::user()->hasPermission('staff.edit'), 403);

        $user = User::findOrFail($id);
        if ($user->id === Auth::id() || $user->isSuperAdmin()) {
            $this->dispatch('toast', type: 'error', message: 'This account cannot be changed.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->dispatch('toast', type: 'success', message: $user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        abort_unless(Auth::user()->hasPermission('staff.delete'), 403);

        $user = User::find($this->deleteId);
        if ($user && $user->id !== Auth::id() && ! $user->isSuperAdmin()) {
            $user->delete();
            $this->dispatch('toast', type: 'success', message: 'Staff member removed.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $currentUser = Auth::user();
        $staff = User::where('id', '!=', Auth::id())->orderBy('name')->get();

        return view('livewire.admin.staff.staff-index', [
            'currentUser' => $currentUser,
            'staff' => $staff,
            'totalStaff' => User::count(),
            'activeCount' => User::where('is_active', true)->count(),
            'adminCount' => User::whereIn('role', ['super_admin', 'admin'])->count(),
            'staffCount' => User::where('role', 'staff')->count(),
        ]);
    }
}
