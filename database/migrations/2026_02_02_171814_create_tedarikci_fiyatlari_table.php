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
        Schema::create('tedarikci_fiyatlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('musteri_id')->constrained('musteriler')->onDelete('cascade');
            $table->foreignId('urun_id')->nullable()->constrained('urunler')->onDelete('set null');
            $table->string('urun_adi');
            $table->date('tarih');
            $table->decimal('birim_fiyat', 15, 2);
            $table->string('para_birimi')->default('TL');
            $table->integer('minimum_siparis')->default(1);
            $table->integer('temin_suresi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->text('notlar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tedarikci_fiyatlari');
    }
};
