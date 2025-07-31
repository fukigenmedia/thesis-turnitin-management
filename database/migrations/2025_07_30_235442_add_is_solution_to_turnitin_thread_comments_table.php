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
        Schema::table('turnitin_thread_comments', function (Blueprint $table) {
            $table->boolean('is_solution')->default(false)->after('file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turnitin_thread_comments', function (Blueprint $table) {
            $table->dropColumn('is_solution');
        });
    }
};
