<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Order extends Model
{
    use HasFactory;

    // Поля, які можуть бути масово заповнені
    protected $fillable = [
        'user_id',
        'product_id',
        'order_number',
        'amount',
        'status',
        'quantity',
    ];

    // Статуси замовлення
    const STATUS_NEW = 'new';
    const STATUS_PACKING = 'packing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';

    /**
     * Зв'язок замовлення з користувачем (багато до одного)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Зв'язок замовлення з товаром (багато до одного)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Локальний обсяг (scope) для фільтрації замовлень за статусом
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Встановлення значень за замовчуванням для нових замовлень
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Наприклад, присвоєння нового статусу за замовчуванням
            $order->status = $order->status ?? self::STATUS_NEW;
        });
    }
}
