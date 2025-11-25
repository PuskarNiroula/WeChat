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
        Schema::create('user_pre_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // reference users table
            $table->text('public_key'); // store public key as base64
            $table->text('signature')->nullable(); // for signed pre-key signature
            $table->enum('key_type', ['signed', 'one-time']); // type of key
            $table->boolean('used')->default(false); // only for one-time keys
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pre_keys');
    }
};
