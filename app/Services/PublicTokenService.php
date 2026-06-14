<?php

namespace App\Services;

use App\Models\PublicToken;
use Illuminate\Support\Str;

class PublicTokenService
{
    /**
     * Return an existing valid token for the record, or create a new one.
     */
    public function getOrCreate(string $type, int $referenceId): string
    {
        $existing = PublicToken::where('type', $type)
            ->where('reference_id', $referenceId)
            ->valid()
            ->first();

        if ($existing) {
            return $existing->token;
        }

        $token = Str::random(64);

        PublicToken::create([
            'token' => $token,
            'type' => $type,
            'reference_id' => $referenceId,
            'expires_at' => null, // never expires
        ]);

        return $token;
    }

    public function getPublicUrl(string $type, int $referenceId): string
    {
        return route('public.view', $this->getOrCreate($type, $referenceId));
    }
}
