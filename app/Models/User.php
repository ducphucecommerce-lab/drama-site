<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_admin', 'is_vip', 'vip_expires_at', 'avatar'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['vip_expires_at' => 'datetime', 'is_admin' => 'boolean', 'is_vip' => 'boolean'];

    public function isVip(): bool
    {
        return $this->is_vip && $this->vip_expires_at && $this->vip_expires_at->isFuture();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class);
    }

    // Kích hoạt VIP sau thanh toán thành công
    public function activateVip(int $days = 30): void
    {
        $this->update([
            'is_vip'          => true,
            'vip_expires_at'  => now()->addDays($days),
        ]);
    }
}
