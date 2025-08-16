<?php

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
        Schema::table('candidatos', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('pode_ser_contactado');
            $table->string('country')->nullable()->after('last_seen_at');
            $table->string('city')->nullable()->after('country');
            $table->string('region')->nullable()->after('city');
            $table->decimal('latitude', 10, 8)->nullable()->after('region');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->boolean('is_online')->default(false)->after('longitude');
            $table->string('avatar_url')->nullable()->after('is_online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatos', function (Blueprint $table) {
            $table->dropColumn([
                'last_seen_at',
                'country',
                'city', 
                'region',
                'latitude',
                'longitude',
                'is_online',
                'avatar_url'
            ]);
        });
    }
};
