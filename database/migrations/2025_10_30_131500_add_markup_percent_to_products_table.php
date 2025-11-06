<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'markup_percent')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('markup_percent', 5, 2)->nullable()->after('selling_price');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'markup_percent')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('markup_percent');
            });
        }
    }
};
