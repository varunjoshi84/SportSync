import { IsNotEmpty, IsNumber, IsOptional, IsBoolean } from 'class-validator';

export class ToggleFavoriteDto {
  @IsNumber()
  @IsNotEmpty({ message: 'Match ID is required' })
  match_id: number;

  @IsOptional()
  @IsBoolean()
  is_favorite?: boolean;
}
