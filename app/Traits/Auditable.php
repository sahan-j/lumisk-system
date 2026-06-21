<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    /** Fields excluded from audit diffs (noise / sensitive data). */
    protected array $auditExclude = [
        'updated_at',
        'created_at',
        'remember_token',
        'password',
        'email_verified_at',
        'last_login_at',
    ];

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::record('created', $model, [], $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $old = [];
            $new = [];

            foreach ($model->getDirty() as $key => $newValue) {
                if (in_array($key, $model->auditExclude, true)) {
                    continue;
                }
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $newValue;
            }

            if (! empty($new)) {
                AuditLog::record('updated', $model, $old, $new);
            }
        });

        static::deleted(function ($model) {
            AuditLog::record('deleted', $model, $model->getAuditableAttributes(), []);
        });

        // Soft-delete restores (only fires on models using SoftDeletes).
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                AuditLog::record('restored', $model, [], $model->getAuditableAttributes());
            });
        }
    }

    /** Model attributes with excluded fields stripped out. */
    public function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();

        foreach ($this->auditExclude as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }
}
