<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStylingColumnsToContentBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->nullable()->after('sequence_no');
            $table->string('class')->nullable()->after('is_active');
            $table->string('margin_top')->nullable()->after('class');
            $table->string('margin_bottom')->nullable()->after('margin_top');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'class',
                'margin_top',
                'margin_bottom',
            ]);
        });
    }
}
