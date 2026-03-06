import { Provider } from '@nestjs/common';
import { createClient, type RedisClientType } from 'redis';

export const redisProvider: Provider = {

    provide: 'REDIS_CLIENT',

    useFactory: async (): Promise<RedisClientType> => {

        const client = createClient({
            socket: {
                host: process.env.REDIS_HOST ?? 'localhost',
                port: parseInt(process.env.REDIS_PORT ?? '6379'),
            },
            password: process.env.REDIS_PASSWORD,
            database: parseInt(process.env.REDIS_DB ?? '0'),

        }) as unknown as RedisClientType;

        client.on('error', (err) => {
            console.error('Redis error:', err);
        });

        client.on('connect', () => {
            console.log('Redis connected');
        });

        await client.connect();
        return client;
    },
};
