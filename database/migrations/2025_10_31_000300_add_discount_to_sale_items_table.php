<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('sale_items') && ! Schema::hasColumn('sale_items', 'discount')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->decimal('discount', 12, 2)->default(0)->after('price');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'discount')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn('discount');
            });
        }
    }
};
