<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')
                ->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('collection_id')->nullable()
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('post_id')->nullable()
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->text('token');
            $table->unsignedTinyInteger('permission')->default(4);
            $table->boolean('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
}
