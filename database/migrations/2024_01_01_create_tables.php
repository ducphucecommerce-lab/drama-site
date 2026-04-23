<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        // Thêm cột vào bảng users có sẵn
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->boolean('is_vip')->default(false)->after('is_admin');
            $table->timestamp('vip_expires_at')->nullable()->after('is_vip');
            $table->string('avatar')->nullable()->after('vip_expires_at');
        });

        // Tạo bảng subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan')->default('vip');
            $table->string('payment_method');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Tạo bảng watch_history
        Schema::create('watch_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('drama_id');
            $table->string('platform');
            $table->string('drama_title');
            $table->string('cover_url')->nullable();
            $table->integer('episode')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('watch_history');
        Schema::dropIfExists('subscriptions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_vip', 'vip_expires_at', 'avatar']);
        });
    }
};