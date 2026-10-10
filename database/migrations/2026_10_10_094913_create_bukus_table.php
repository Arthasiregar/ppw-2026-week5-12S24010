<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukus', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 17)->unique();
            $table->string('judul', 200)->index();
            $table->string('penulis', 150);
            $table->string('penerbit', 150);
            $table->unsignedSmallInteger('tahun_terbit');
            $table->foreignId('kategori_id')->constrained('kategoris')->restrictOnDelete();
            $table->unsignedInteger('stok')->default(0);
            $table->text('sinopsis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukus');
    }
};