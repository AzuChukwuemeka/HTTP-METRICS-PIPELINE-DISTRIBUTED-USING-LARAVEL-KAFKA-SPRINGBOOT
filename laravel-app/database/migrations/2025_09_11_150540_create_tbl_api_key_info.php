<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('tbl_api_key_usage_logs', function (Blueprint $table) {
            $table->uuid("api_info_id")->primary();
            $table->uuid("api_id");
            $table
                ->foreign("api_id")
                ->references("api_id")
                ->on("tbl_api_keys")
                ->onDelete('cascade');
            $table->string("event_type");
            $table->string("endpoint");
            $table->integer("status_code");
        });
    }
    public function down() : void
    {
        Schema::dropIfExists('tbl_api_key_info');
    }
};
