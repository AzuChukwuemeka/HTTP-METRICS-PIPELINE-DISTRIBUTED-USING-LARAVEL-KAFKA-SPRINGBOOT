<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tbl_events', function (Blueprint $table) {
            $table->uuid("event_id")->primary();
            $table->string("event_type");
            $table->string("url");
            $table->timestamp("time-bucket");
            $table->unsignedBigInteger("count");
            $table->unique(["event_type", "url", "time-bucket"]);
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
        Schema::dropIfExists('tbl_events');
    }
};
