<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [];

    public const DEFAULT_WHATSAPP_INVOICE = "Dear {client_name},\n\nPlease find your Invoice *{invoice_number}* for *LKR {total}*.\n\n📎 View & Download: {link}\n\nDue Date: {due_date}\n\nThank you for your business!\n_{company_name}_\n📞 {company_phone}";

    public const DEFAULT_WHATSAPP_ESTIMATE = "Dear {client_name},\n\nPlease find your Estimate *{estimate_number}* for *LKR {total}*.\n\n📎 View & Download: {link}\n\nValid Until: {expiry_date}\n\nThank you for your business!\n_{company_name}_\n📞 {company_phone}";

    protected function casts(): array
    {
        return [
            'invoice_next_number' => 'integer',
            'estimate_next_number' => 'integer',
            'estimate_expiry_days' => 'integer',
            'default_tax_rate' => 'decimal:2',
            'overdue_reminders_enabled' => 'boolean',
        ];
    }

    /**
     * Get the single company settings row, creating it if missing.
     */
    public static function settings(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return asset('storage/' . $this->logo);
    }
}
