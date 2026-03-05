import { Module } from '@nestjs/common';
import { ClientsModule, Transport } from '@nestjs/microservices';
import { InventoryService } from '../inventory/inventory.service';
import { EVENTS_CLIENT } from './events.constants';
import { OrderEventsController } from './order-events.controller';
import { OrderEventsPublisher } from './order-events.publisher';

@Module({
  imports: [
    ClientsModule.register([
      {
        name: EVENTS_CLIENT,
        transport: Transport.TCP,
        options: {
          host: process.env.EVENTS_HOST ?? '127.0.0.1',
          port: Number(process.env.EVENTS_PORT ?? 4001),
        },
      },
    ]),
  ],
  controllers: [OrderEventsController],
  providers: [OrderEventsPublisher, InventoryService],
  exports: [OrderEventsPublisher],
})
export class EventsModule {}
