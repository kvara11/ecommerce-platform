import { Injectable } from '@nestjs/common';
import { DataSource, Repository } from 'typeorm';
import { InjectRepository } from '@nestjs/typeorm';
import { OrderEntity } from './entities/order.entity';
import { CreateOrderDto } from './dto/create-order.dto';
import { OrderEventsPublisher } from '../events/order-events.publisher';

@Injectable()
export class OrdersService {
  constructor(
    private readonly dataSource: DataSource,
    @InjectRepository(OrderEntity)
    private readonly orderRepository: Repository<OrderEntity>,
    private readonly orderEventsPublisher: OrderEventsPublisher,
  ) {}

  async createOrder(createOrderDto: CreateOrderDto): Promise<OrderEntity> {
    const createdOrder = await this.dataSource.transaction(async (manager) => {
      const order = manager.create(OrderEntity, {
        user_id: createOrderDto.userId,
        total_amount: createOrderDto.totalAmount,
        items: createOrderDto.items,
      });

      return manager.save(order);
    });

    // Event-driven publishing keeps order creation focused on the write path,
    // while side effects like emails/inventory can evolve independently.
    this.orderEventsPublisher.publishOrderCreated({
      orderId: createdOrder.id,
      userId: createdOrder.user_id,
      totalAmount: Number(createdOrder.total_amount),
      items: createdOrder.items,
    });

    return createdOrder;
  }
}
