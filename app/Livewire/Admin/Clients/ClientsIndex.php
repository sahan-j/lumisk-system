<?php

namespace App\Livewire\Admin\Clients;

use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Clients')]
class ClientsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $portal = '';

    // Form / modal state
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $company_name = '';
    public string $default_currency = 'LKR';
    public bool $portal_enabled = false;
    public string $password = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPortal(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $client = Client::findOrFail($id);
        $this->editingId = $client->id;
        $this->name = $client->name;
        $this->email = $client->email;
        $this->phone = (string) $client->phone;
        $this->address = (string) $client->address;
        $this->company_name = (string) $client->company_name;
        $this->default_currency = $client->default_currency ?: 'LKR';
        $this->portal_enabled = (bool) $client->portal_enabled;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission($this->editingId ? 'clients.edit' : 'clients.create'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($this->editingId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'default_currency' => ['required', 'string', 'max:3'],
            'portal_enabled' => ['boolean'],
            'password' => [
                $this->portal_enabled && ! $this->editingId ? 'required' : 'nullable',
                'string',
                'min:6',
            ],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'default_currency' => $validated['default_currency'] ?: 'LKR',
            'portal_enabled' => $this->portal_enabled,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $client = Client::findOrFail($this->editingId);
            $client->update($data);
            ActivityLog::log('client_updated', "Client {$client->name} updated",
                ['subject_type' => 'Client', 'subject_id' => $client->id,
                 'subject_label' => $client->name, 'client_id' => $client->id]);
            $message = 'Client updated.';
        } else {
            $client = Client::create($data);
            ActivityLog::log('client_created', "Client {$client->name} added",
                ['subject_type' => 'Client', 'subject_id' => $client->id,
                 'subject_label' => $client->name, 'client_id' => $client->id]);
            $message = 'Client created.';
        }

        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('clients.delete'), 403);

        if ($this->deleteId) {
            Client::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Client deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'address', 'company_name', 'default_currency', 'portal_enabled', 'password']);
        $this->resetValidation();
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('company_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->portal !== '', fn ($q) => $q->where('portal_enabled', $this->portal === 'enabled'))
            ->withCount(['invoices', 'estimates'])
            ->latest()
            ->paginate(15);

        return view('livewire.admin.clients.clients-index', compact('clients'));
    }
}
