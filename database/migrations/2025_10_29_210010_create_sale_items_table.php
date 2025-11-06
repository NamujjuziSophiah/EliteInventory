<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// Archived duplicate migration — intentionally a no-op to preserve history
return new class extends Migration {
    public function up()
    {
        // no-op
        if (! Schema::hasTable('sale_items')) {
            // archived duplicate: do not create table here
        }
    }

    public function down()
    {
        // no-op
    }
};
