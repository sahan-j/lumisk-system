<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('theme_preset')->nullable()->after('pdf_font_size');
            $table->string('theme_color_1', 7)->default('#00d4ff')->after('theme_preset');
            $table->string('theme_color_2', 7)->default('#6d5cff')->after('theme_color_1');
            $table->string('theme_sidebar_bg', 7)->default('#0f0f0f')->after('theme_color_2');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['theme_preset', 'theme_color_1', 'theme_color_2', 'theme_sidebar_bg']);
        });
    }
};
