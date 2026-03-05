<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrdersViewController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status_id', 'payment_status_id', 'date_from', 'date_to']);
        $orders = $this->orderService->paginate($filters, perPage: 15);

        // Transform orders for Vue component
        $ordersData = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'first_name' => $order->user->first_name,
                    'last_name' => $order->user->last_name,
                    'email' => $order->user->email,
                ] : null,
                'status' => $order->status ? [
                    'id' => $order->status->id,
                    'name' => $order->status->name,
                    'label' => $order->status->label,
                ] : null,
                'payment_status' => $order->paymentStatus ? [
                    'id' => $order->paymentStatus->id,
                    'name' => $order->paymentStatus->name,
                    'label' => $order->paymentStatus->label,
                ] : null,
                'payment_method' => $order->paymentMethod ? [
                    'id' => $order->paymentMethod->id,
                    'name' => $order->paymentMethod->name,
                ] : null,
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'shipping_amount' => $order->shipping_amount,
                'discount_amount' => $order->discount_amount,
                'total_amount' => $order->total_amount,
                'currency' => $order->currency,
                'items_count' => $order->items()->count(),
                'created_at' => $order->created_at,
                'shipped_at' => $order->shipped_at,
                'delivered_at' => $order->delivered_at,
                'cancelled_at' => $order->cancelled_at,
            ];
        });

        // Get all statuses and payment statuses for filters/dropdowns
        $orderStatuses = OrderStatus::all();
        $paymentStatuses = PaymentStatus::all();

        return Inertia::render('Orders/Index', [
            'orders' => [
                'data' => $ordersData,
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
            ],
            'orderStatuses' => $orderStatuses,
            'paymentStatuses' => $paymentStatuses,
            'filters' => $filters,
        ]);
    }

    /**
     * Display order details.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'status', 'paymentStatus', 'paymentMethod', 'items.product']);

        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer' => $order->user ? [
                'id' => $order->user->id,
                'first_name' => $order->user->first_name,
                'last_name' => $order->user->last_name,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
            ] : null,
            'status' => $order->status ? [
                'id' => $order->status->id,
                'name' => $order->status->name,
                'label' => $order->status->label,
            ] : null,
            'payment_status' => $order->paymentStatus ? [
                'id' => $order->paymentStatus->id,
                'name' => $order->paymentStatus->name,
                'label' => $order->paymentStatus->label,
            ] : null,
            'payment_method' => $order->paymentMethod ? [
                'id' => $order->paymentMethod->id,
                'name' => $order->paymentMethod->name,
            ] : null,
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'shipping_amount' => $order->shipping_amount,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'notes' => $order->notes,
            'customer_notes' => $order->customer_notes,
            'created_at' => $order->created_at,
            'shipped_at' => $order->shipped_at,
            'delivered_at' => $order->delivered_at,
            'cancelled_at' => $order->cancelled_at,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'product_sku' => $item->product?->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'tax_amount' => $item->tax_amount,
                    'discount_amount' => $item->discount_amount,
                    'total_amount' => $item->total_amount,
                ];
            }),
        ];

        $orderStatuses = OrderStatus::all();
        $paymentStatuses = PaymentStatus::all();

        return Inertia::render('Orders/Show', [
            'order' => $orderData,
            'orderStatuses' => $orderStatuses,
            'paymentStatuses' => $paymentStatuses,
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        try {
            $validated = $request->validated();

            // Update status if provided
            if (!empty($validated['status_id'])) {
                $this->orderService->updateStatus($order, $validated['status_id']);
            }

            // Update payment status if provided
            if (!empty($validated['payment_status_id'])) {
                $this->orderService->updatePaymentStatus($order, $validated['payment_status_id']);
            }

            // Update other fields
            $updateData = collect($validated)->only(['notes', 'shipping_address', 'billing_address'])->toArray();
            if (!empty($updateData)) {
                $order->update($updateData);
            }

            return back()->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
