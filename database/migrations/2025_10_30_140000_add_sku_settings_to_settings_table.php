<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (! Schema::hasColumn('settings', 'sku_prefix')) {
                    $table->string('sku_prefix')->default('PR')->after('default_markup_percent');
                }
                if (! Schema::hasColumn('settings', 'sku_padding')) {
                    $table->integer('sku_padding')->default(6)->after('sku_prefix');
                }
                if (! Schema::hasColumn('settings', 'sku_next')) {
                    $table->bigInteger('sku_next')->default(1)->after('sku_padding');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'sku_prefix')) {
                    $table->dropColumn('sku_prefix');
                }
                if (Schema::hasColumn('settings', 'sku_padding')) {
                    $table->dropColumn('sku_padding');
                }
                if (Schema::hasColumn('settings', 'sku_next')) {
                    $table->dropColumn('sku_next');
                }
            });
        }
    }
};
