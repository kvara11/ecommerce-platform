import { Controller, Logger } from '@nestjs/common';
import { EventPattern, Payload } from '@nestjs/microservices';
import { InventoryService } from '../inventory/inventory.service';
import type { OrderCreatedEvent } from './contracts/order-created.event';
import { ORDER_CREATED_EVENT } from './events.constants';

@Controller()
export class OrderEventsController {
  private readonly logger = new Logger(OrderEventsController.name);

  constructor(private readonly inventoryService: InventoryService) {}

  @EventPattern(ORDER_CREATED_EVENT)
  async handleOrderCreated(@Payload() event: OrderCreatedEvent): Promise<void> {
    this.logger.log(
      `Mock email queued: orderId=${event.orderId}, userId=${event.userId}, totalAmount=${event.totalAmount}`,
    );

    await this.inventoryService.decrementStockForOrderItems(event.items);
  }
}
