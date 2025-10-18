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

            $table->string('type', 20)->index();

            $table->text('url')->nullable();
            $table->text('base_url')->nullable();

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('content');

            $table->string('color', 40)->nullable();
            $table->string('image_path')->nullable();

            $table->unsignedBigInteger('order')->index();

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
