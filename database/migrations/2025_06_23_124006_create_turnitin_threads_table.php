<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turnitin_threads', function (Blueprint $table) {
            $table->id();
            $table->dateTime('datetime');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('lecture_id');
            $table->tinyInteger('status')->default(0);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_original_name')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnitin_threads');
    }
};
