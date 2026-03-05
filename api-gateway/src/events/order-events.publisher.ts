import { Inject, Injectable, Logger } from '@nestjs/common';
import { ClientProxy } from '@nestjs/microservices';
import { OrderCreatedEvent } from './contracts/order-created.event';
import { EVENTS_CLIENT, ORDER_CREATED_EVENT } from './events.constants';

@Injectable()
export class OrderEventsPublisher {
  private readonly logger = new Logger(OrderEventsPublisher.name);

  constructor(
    @Inject(EVENTS_CLIENT)
    private readonly eventsClient: ClientProxy,
  ) {}

  publishOrderCreated(payload: OrderCreatedEvent): void {
    this.eventsClient.emit<OrderCreatedEvent>(ORDER_CREATED_EVENT, payload).subscribe({
      error: (error) => {
        this.logger.error(`Failed to emit ${ORDER_CREATED_EVENT}`, error);
      },
    });
  }
}
