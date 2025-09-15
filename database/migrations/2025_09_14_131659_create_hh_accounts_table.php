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
        Schema::create('hh_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('hh_user_id')->index();            // id соискателя в hh
            $t->string('token_type')->default('Bearer');
            $t->text('access_token');
            $t->text('refresh_token')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->json('profile')->nullable();              // кэш профиля /me
            $t->json('token_payload')->nullable();        // что вернул /oauth/token
            $t->timestamps();

            $t->unique(['user_id', 'hh_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hh_accounts');
    }
};
