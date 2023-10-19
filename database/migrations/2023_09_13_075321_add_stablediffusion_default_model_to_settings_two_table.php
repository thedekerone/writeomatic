<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings_two', function (Blueprint $table) {
            if (Schema::hasColumn('settings_two', 'stablediffusion_default_model')) {
                $table->string('stablediffusion_default_model')->default('stable-diffusion-512-v2-1')->change();
            } else {
                $table->string('stablediffusion_default_model')->default('stable-diffusion-512-v2-1');
            }

        });
    }
    public function down(): void
    {
        Schema::table('settings_two', function (Blueprint $table) {
            $table->string('stablediffusion_default_model')->default('stable-diffusion-512-v2-1')->change();

        });
    }
};
