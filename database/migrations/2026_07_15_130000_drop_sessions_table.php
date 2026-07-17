<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Custom token-auth table, superseded by Sanctum's personal_access_tokens.
// Unrelated to Laravel's native session driver (SESSION_DRIVER=file here).
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sessions');
    }

    public function down(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->timestamp('expired_at');
            $table->timestamps();
        });
    }
};
