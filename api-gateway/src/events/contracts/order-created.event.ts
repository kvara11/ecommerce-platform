export interface OrderCreatedEventItem {
  productId: number;
  quantity: number;
}

export interface OrderCreatedEvent {
  orderId: number;
  userId: number;
  totalAmount: number;
  items: OrderCreatedEventItem[];
}
