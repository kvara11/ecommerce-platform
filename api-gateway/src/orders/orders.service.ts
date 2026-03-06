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

  async findAll(filters: {
    userId?: number;
    page?: number;
    limit?: number;
  }) {
    const page = filters.page ?? 1;
    const limit = filters.limit ?? 20;
    const skip = (page - 1) * limit;

    const query = this.orderRepository
      .createQueryBuilder('order')
      .leftJoinAndSelect('order.items', 'items');

    if (filters.userId) {
      query.andWhere('order.user_id = :userId', { userId: filters.userId });
    }

    query.orderBy('order.created_at', 'DESC').skip(skip).take(limit);

    const [data, total] = await query.getManyAndCount();

    return {
      data,
      pagination: {
        total,
        page,
        limit,
        pages: Math.ceil(total / limit),
      },
    };
  }

  async findOne(id: number): Promise<OrderEntity | null> {
    return this.orderRepository.findOne({ where: { id } });
  }

  async createOrder(createOrderDto: CreateOrderDto): Promise<OrderEntity> {
    const createdOrder = await this.dataSource.transaction(async (manager) => {
      const order = manager.create(OrderEntity, {
        user_id: createOrderDto.userId,
        total_amount: createOrderDto.totalAmount,
        items: createOrderDto.items,
      });

      return manager.save(order);
    });

    this.orderEventsPublisher.publishOrderCreated({
      orderId: createdOrder.id,
      userId: createdOrder.user_id,
      totalAmount: Number(createdOrder.total_amount),
      items: createdOrder.items.map((item) => ({
        productId: item.product_id,
        quantity: item.quantity,
      })),
    });

    return createdOrder;
  }
}
