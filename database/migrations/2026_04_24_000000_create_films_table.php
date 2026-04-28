<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('drama_id')->index();
            $table->string('platform')->index();
            $table->string('lang', 10)->default('vi')->index();
            $table->string('title');
            $table->string('author')->nullable();
            $table->text('cover')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('status')->nullable();
            $table->string('views')->nullable();
            $table->integer('chapters')->default(0);
            $table->json('genres')->nullable();
            $table->json('tags')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['drama_id', 'platform', 'lang']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('films');
    }
};
