import { Injectable, Logger } from '@nestjs/common';
import { OrderCreatedEventItem } from '../events/contracts/order-created.event';

@Injectable()
export class InventoryService {
  private readonly logger = new Logger(InventoryService.name);

  async decrementStock(productId: number, quantity: number): Promise<void> {
    this.logger.log(`Inventory updated: productId=${productId}, decrementedBy=${quantity}`);
  }

  async decrementStockForOrderItems(items: OrderCreatedEventItem[]): Promise<void> {
    for (const item of items) {
      await this.decrementStock(item.productId, item.quantity);
    }
  }
}
