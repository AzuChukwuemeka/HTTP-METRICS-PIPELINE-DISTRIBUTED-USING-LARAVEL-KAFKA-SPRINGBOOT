<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_api_keys', function (Blueprint $table) {
            $table->uuid("api_id")->primary();
            $table->uuid("user_id");
            $table
                ->foreign("user_id")
                ->references("user_id")
                ->on("tbl_users")
                ->onDelete('cascade');
            $table->string("key")->unique();
            $table->string("name")->unique();
            $table->boolean("active")->nullable();
            $table->timestamp("expires_at")->nullable();
            $table->timestamp("last_used_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys_tbl');
    }
};
