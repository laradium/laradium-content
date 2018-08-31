<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_active')->default(0);
            $table->boolean('is_homepage')->default(0);
            $table->string('content_type')->nullable();
            $table->integer('content_id')->nullable();


            $table->string('meta_image_file_name')->nullable();
            $table->integer('meta_image_file_size')->nullable();
            $table->string('meta_image_content_type')->nullable();
            $table->timestamp('meta_image_updated_at')->nullable();

            $table->timestamps();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('slug')->nullable()->index();

            $table->text('meta_keywords')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_url')->nullable();

            $table->string('locale')->nullable()->index();

            $table->unsignedInteger('page_id')->nullable();
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
}
