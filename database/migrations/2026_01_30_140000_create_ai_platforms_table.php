<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Platform adı (ChatGPT, Claude, vb)
            $table->string('plan')->nullable(); // Plan adı (Pro, Plus, Team)
            $table->decimal('monthly_cost', 10, 2)->nullable(); // Aylık maliyet
            $table->string('currency')->default('USD'); // Döviz
            $table->date('subscription_date')->nullable(); // Başlangıç tarihi
            $table->date('renewal_date')->nullable(); // Yenileme tarihi
            $table->string('status')->default('active'); // active, paused, cancelled
            $table->text('notes')->nullable(); // Notlar
            $table->string('website')->nullable(); // Website URL
            $table->string('api_key')->nullable(); // API key (şifrelenmiş)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_platforms');
    }
};
