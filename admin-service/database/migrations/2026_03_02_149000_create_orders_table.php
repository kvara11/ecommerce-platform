<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('status_id')->constrained('order_statuses')->restrictOnDelete();
            $table->foreignId('payment_status_id')->constrained('payment_statuses')->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->restrictOnDelete();
            $table->string('shipping_address')->nullable();
            $table->string('billing_address')->nullable();
            $table->decimal('subtotal', 10, 2)->comment('Total amount of items before tax');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('Total tax amount');
            $table->decimal('shipping_amount', 10, 2)->default(0)->comment('Total shipping amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('Total discount amount');
            $table->decimal('total_amount', 10, 2)->comment('Total amount of the order');
            $table->string('currency', 3)->default('GEL');
            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_number', 'user_id', 'status_id', 'payment_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
