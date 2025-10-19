<?php

use App\Enums\SharePermissionEnum;
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

            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('guest_id')
                ->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('collection_id')
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->unsignedTinyInteger('permission')->default(SharePermissionEnum::Read->value);

            $table->timestamps();

            $table->unique(['user_id', 'guest_id', 'collection_id']);
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
