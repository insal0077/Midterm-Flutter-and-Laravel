<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('category_id');                 // BIGINT(20) UNSIGNED, AUTO_INCREMENT, PRIMARY KEY
            $table->string('category_name', 50);                  // VARCHAR(50), NOT NULL
            $table->string('description', 255)->nullable();       // VARCHAR(255), NULL
            $table->string('image', 255);                         // VARCHAR(255), NOT NULL
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); // NOT NULL, default CURRENT_TIMESTAMP
            $table->timestamp('updated_at')->nullable();          // NULL
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
