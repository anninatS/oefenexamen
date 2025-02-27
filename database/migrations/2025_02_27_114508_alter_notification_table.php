<?php

use App\Models\Notification;
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
        Schema::table('notifications', function (Blueprint $table) {
            // Convert the current types to allowed types
            foreach (Notification::all() as $notification) {
                if (!in_array($notification->type, Notification::TYPES)) {
                    $notification->type = Notification::TYPE_SYSTEM;
                    $notification->save();
                }
            }

            $table->enum('type', Notification::TYPES)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->change();
        });
    }
};
