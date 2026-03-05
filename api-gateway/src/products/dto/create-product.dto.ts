import { IsString, IsNumber, IsOptional, Min, Max, IsInt } from 'class-validator';

export class CreateProductDto {
    @IsString()
    name: string;

    @IsString()
    @IsOptional()
    description?: string;

    @IsNumber({ maxDecimalPlaces: 2 })
    @Min(0)
    @Max(1000000)
    price: number;

    @IsInt()
    @Min(0)
    quantity: number;

    @IsInt()
    category_id: number;

    @IsString()
    sku: string;
}
