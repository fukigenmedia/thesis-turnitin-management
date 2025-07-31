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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('push_notifications_enabled')->default(false);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('thread_created_notifications')->default(true);
            $table->boolean('new_comment_notifications')->default(true);
            $table->boolean('solution_marked_notifications')->default(true);
            $table->boolean('thread_status_notifications')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'push_notifications_enabled',
                'email_notifications_enabled',
                'thread_created_notifications',
                'new_comment_notifications',
                'solution_marked_notifications',
                'thread_status_notifications',
            ]);
        });
    }
};
