<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('vip_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 1month, 3months, 6months
            $table->string('name');
            $table->integer('days');
            $table->decimal('price', 8, 2);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default plans
        DB::table('vip_plans')->insert([
            ['key'=>'1month',  'name'=>'1 Month',  'days'=>30,  'price'=>5,  'is_featured'=>false, 'is_active'=>true, 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'3months', 'name'=>'3 Months', 'days'=>90,  'price'=>12, 'is_featured'=>true,  'is_active'=>true, 'created_at'=>now(), 'updated_at'=>now()],
            ['key'=>'6months', 'name'=>'6 Months', 'days'=>180, 'price'=>20, 'is_featured'=>false, 'is_active'=>true, 'created_at'=>now(), 'updated_at'=>now()],
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('vip_plans');
    }
};
