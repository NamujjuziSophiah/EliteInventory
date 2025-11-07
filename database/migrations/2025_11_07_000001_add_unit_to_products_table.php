<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'unit')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('unit')->nullable()->after('image_path');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'unit')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('unit');
            });
        }
    }
};
