<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('distributor_id')->constrained()->restrictOnDelete();
            $t->string('nama');
            $t->string('alamat');
            $t->string('telepon');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};