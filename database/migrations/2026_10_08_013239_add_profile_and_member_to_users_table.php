<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('avatar')->nullable()->after('phone');
            $table->text('address')->nullable()->after('avatar');
            $table->text('bio')->nullable()->after('address');
            $table->boolean('is_member')->default(false)->after('bio');
            $table->unsignedBigInteger('points')->default(0)->after('is_member');
            $table->string('member_tier')->default('bronze')->after('points');
            $table->timestamp('member_joined_at')->nullable()->after('member_tier');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'address',
                'bio',
                'is_member',
                'points',
                'member_tier',
                'member_joined_at',
            ]);
        });
    }
};