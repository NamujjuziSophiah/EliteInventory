<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        // Add 'quantity' column if missing
        if (! Schema::hasColumn('products', 'quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('quantity')->unsigned()->default(0);
            });

            // Populate from common alternate columns if present
            if (Schema::hasColumn('products', 'qty')) {
                DB::table('products')->update(['quantity' => DB::raw('COALESCE(qty, 0)')]);
            } elseif (Schema::hasColumn('products', 'stock')) {
                DB::table('products')->update(['quantity' => DB::raw('COALESCE(stock, 0)')]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};
