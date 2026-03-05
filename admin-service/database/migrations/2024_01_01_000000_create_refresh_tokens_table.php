<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CreateRefreshTokensTable Migration
 * 
 * Run: php artisan migrate
 * 
 * Creates the refresh_tokens table used by NestJS API Gateway.
 * Stores bcrypt-hashed refresh tokens linked to users.
 * 
 * Both NestJS and Laravel can access this table to:
 * - Verify refresh tokens
 * - Revoke/invalidate tokens
 * - Clean up expired tokens
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('user_id');
            $table->text('token_hash');
            $table->timestamp('expires_at');
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('expires_at');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
