<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_journals', function (Blueprint $table) {
            $table->id();
            $table->string('task_key', 128)->nullable()->index();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->string('actor', 64)->default('unknown')->index(); // codex, copilot, manuel
            $table->string('status', 32)->default('pending')->index(); // pending, success, fail
            $table->text('summary');
            $table->string('commit_hash', 64)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['task_key', 'attempt_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_journals');
    }
};

