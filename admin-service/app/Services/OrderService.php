<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;

class OrderService
{
    
    public function paginate(array $filters, int $perPage = 15)
    {
        $query = Order::query()->with([
            'user',
            'status',
            'paymentStatus',
            'paymentMethod',
            'items.product'
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ILIKE', "%{$search}%")
                  ->orWhere('notes', 'ILIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'ILIKE', "%{$search}%")
                         ->orWhere('last_name', 'ILIKE', "%{$search}%")
                         ->orWhere('email', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (!empty($filters['payment_status_id'])) {
            $query->where('payment_status_id', $filters['payment_status_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }


    public function updateStatus(Order $order, int $statusId): Order
    {
        $order->update(['status_id' => $statusId]);

        // Update timestamps based on status
        $status = OrderStatus::find($statusId);
        if ($status && $status->name === 'shipped') {
            $order->update(['shipped_at' => now()]);
        } elseif ($status && $status->name === 'delivered') {
            $order->update(['delivered_at' => now()]);
        } elseif ($status && $status->name === 'cancelled') {
            $order->update(['cancelled_at' => now()]);
        }

        return $order->load([
            'user',
            'status',
            'paymentStatus',
            'paymentMethod',
            'items.product'
        ]);
    }


    public function updatePaymentStatus(Order $order, int $paymentStatusId): Order
    {
        $order->update(['payment_status_id' => $paymentStatusId]);
        return $order->load([
            'user',
            'status',
            'paymentStatus',
            'paymentMethod',
            'items.product'
        ]);
    }


    public function getById(int $id): ?Order
    {
        return Order::with([
            'user',
            'status',
            'paymentStatus',
            'paymentMethod',
            'items.product'
        ])->find($id);
    }


    public function getOrderStatuses()
    {
        return OrderStatus::all();
    }


    public function getPaymentStatuses()
    {
        return PaymentStatus::all();
    }


    /**
     * Calculate order summary
     */
    public function getOrderSummary(Order $order): array
    {
        $itemsCount = $order->items()->count();
        $itemsTotal = $order->items()->sum('quantity');

        return [
            'items_count' => $itemsCount,
            'items_total_quantity' => $itemsTotal,
            'subtotal' => $order->subtotal,
            'tax' => $order->tax_amount,
            'shipping' => $order->shipping_amount,
            'discount' => $order->discount_amount,
            'total' => $order->total_amount,
            'currency' => $order->currency,
        ];
    }
}
