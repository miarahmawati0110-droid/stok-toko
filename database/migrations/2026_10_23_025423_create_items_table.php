<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $t) {
            $t->id();
            $t->string('kode')->unique();
            $t->string('nama');
            $t->string('satuan');
            $t->unsignedBigInteger('harga');
            $t->unsignedInteger('stok')->default(0);
            $t->date('tanggal_masuk');
            $t->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $t->foreignId('distributor_id')->constrained()->restrictOnDelete();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};