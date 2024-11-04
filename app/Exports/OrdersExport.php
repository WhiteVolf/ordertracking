<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Вибираємо всі замовлення користувача
        return Order::where('user_id', auth()->id())->get([
            'id', 'product_name', 'order_number', 'amount', 'status', 'created_at', 'updated_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Order Number',
            'Amount',
            'Status',
            'Created At',
            'Updated At'
        ];
    }
}
