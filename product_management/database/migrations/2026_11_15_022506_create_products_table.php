<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('ProductID');                    // BIGINT(20) UNSIGNED, AUTO_INCREMENT, PRIMARY KEY
            $table->string('ProductName', 100);                    // VARCHAR(100), NOT NULL
            $table->string('Description', 255)->nullable();       // VARCHAR(255), NULL
            $table->unsignedBigInteger('CategoryID');              // BIGINT(20) UNSIGNED, NOT NULL
            $table->string('Barcode', 255)->nullable();           // VARCHAR(255), NULL
            $table->date('Expiredate')->nullable();                // DATE, NULL
            $table->integer('Qty')->default(0);                    // INT(11), default 0
            $table->decimal('UnitPriceIn', 10, 2)->default(0.00);  // DECIMAL(10,2), default 0.00
            $table->decimal('UnitPriceOut', 10, 2)->default(0.00); // DECIMAL(10,2), default 0.00
            $table->string('ProductImage', 255)->nullable();       // VARCHAR(255), NULL
            $table->boolean('isDeleted')->default(0);              // TINYINT(1), default 0

            // CategoryID -> categories.category_id
            $table->foreign('CategoryID')->references('category_id')->on('categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
