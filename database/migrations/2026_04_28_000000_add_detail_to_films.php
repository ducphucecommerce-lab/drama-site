<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('films', function (Blueprint $table) {
            $table->json('detail_data')->nullable()->after('raw_data');
            $table->boolean('detail_fetched')->default(false)->after('detail_data');
        });
    }
    public function down(): void {
        Schema::table('films', function (Blueprint $table) {
            $table->dropColumn(['detail_data', 'detail_fetched']);
        });
    }
};
