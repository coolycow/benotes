<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('collection_id')->nullable()
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->text('content');
            $table->tinyInteger('type');
            $table->text('url')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('color', 40)->nullable();
            $table->string('image_path')->nullable();
            $table->string('base_url')->nullable();
            $table->unsignedSmallInteger('order');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
