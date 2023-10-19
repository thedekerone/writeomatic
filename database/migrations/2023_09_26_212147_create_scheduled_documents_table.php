<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduled_documents', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_executed')->default(false);
            $table->timestamp('run_at');
            $table->timestamps();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->foreign('document_id')->references('id')->on('user_openai')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('integrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_documents');
    }
};
