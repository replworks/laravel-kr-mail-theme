<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kr_mail_consents', function (Blueprint $table) {
            $table->id();
            $table->string('user_identifier')->unique();
            $table->boolean('marketing_consent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kr_mail_consents');
    }
};
