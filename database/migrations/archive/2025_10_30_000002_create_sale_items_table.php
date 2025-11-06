<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id')->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->integer('qty')->default(1);
                $table->decimal('price', 12, 2)->default(0);
                $table->decimal('cost_per_unit', 12, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sale_items');
    }
};
