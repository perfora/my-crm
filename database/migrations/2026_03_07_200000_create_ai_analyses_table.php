<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('analysis_type', 50);
            $table->string('source_page')->nullable();
            $table->string('prompt_key', 100);
            $table->unsignedInteger('prompt_version')->default(1);
            $table->json('request_payload')->nullable();
            $table->longText('response_text')->nullable();
            $table->json('response_meta')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['analysis_type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
