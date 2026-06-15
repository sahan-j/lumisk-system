<?php

namespace App\Livewire\Admin\Expenses;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Expense')]
class ExpenseForm extends Component
{
    use WithFileUploads;

    public ?Expense $expense = null;

    public string $title = '';
    public ?string $description = null;
    public ?float $amount = null;
    public string $expense_date = '';
    public ?int $category_id = null;
    public ?int $client_id = null;
    public ?int $project_id = null;
    public string $payment_method = 'cash';
    public ?string $reference_number = null;
    public bool $is_billable = false;
    public ?string $notes = null;

    public $receipt = null; // new upload
    public ?string $existingReceipt = null;

    public function mount(?Expense $expense = null): void
    {
        if ($expense && $expense->exists) {
            $this->expense = $expense;
            $this->title = $expense->title;
            $this->description = $expense->description;
            $this->amount = (float) $expense->amount;
            $this->expense_date = $expense->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $this->category_id = $expense->category_id;
            $this->client_id = $expense->client_id;
            $this->project_id = $expense->project_id;
            $this->payment_method = $expense->payment_method;
            $this->reference_number = $expense->reference_number;
            $this->is_billable = (bool) $expense->is_billable;
            $this->notes = $expense->notes;
            $this->existingReceipt = $expense->receipt;
        } else {
            $this->expense_date = now()->format('Y-m-d');
            $this->client_id = (int) request('client') ?: null;
            $this->project_id = (int) request('project') ?: null;
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'category_id' => ['nullable', 'exists:expense_categories,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'payment_method' => ['required', 'in:' . implode(',', Expense::PAYMENT_METHODS)],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'is_billable' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function removeReceipt(): void
    {
        if ($this->expense && $this->existingReceipt) {
            Storage::disk('public')->delete($this->existingReceipt);
            $this->expense->update(['receipt' => null]);
            $this->existingReceipt = null;
            $this->dispatch('toast', type: 'success', message: 'Receipt removed.');
        }
    }

    public function save()
    {
        $validated = $this->validate();

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'category_id' => $validated['category_id'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'is_billable' => $this->is_billable,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($this->receipt) {
            if ($this->expense && $this->existingReceipt) {
                Storage::disk('public')->delete($this->existingReceipt);
            }
            $data['receipt'] = $this->receipt->store('receipts', 'public');
        }

        if ($this->expense) {
            $this->expense->update($data);
            $message = 'Expense updated!';
        } else {
            $expense = Expense::create($data);
            ActivityLog::log('expense_recorded',
                "Expense \"{$expense->title}\" recorded — " . money($expense->amount),
                ['subject_type' => 'Expense', 'subject_id' => $expense->id,
                 'subject_label' => money($expense->amount, false), 'client_id' => $expense->client_id]);
            $message = 'Expense recorded successfully!';
        }

        $this->dispatch('toast', type: 'success', message: $message);

        return $this->redirect(route('admin.expenses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.expenses.expense-form', [
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company_name']),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_id']),
            'methods' => Expense::PAYMENT_METHODS,
        ]);
    }
}
