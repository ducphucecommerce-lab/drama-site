<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipPlan extends Model
{
    protected $fillable = ['key', 'name', 'days', 'price', 'is_featured', 'is_active'];
    protected $casts    = ['is_featured' => 'boolean', 'is_active' => 'boolean', 'price' => 'decimal:2'];
}
