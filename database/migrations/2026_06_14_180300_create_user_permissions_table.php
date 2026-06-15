<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission_name');
            $table->boolean('granted')->default(true);
            $table->primary(['user_id', 'permission_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
