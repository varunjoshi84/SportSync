import { IsOptional, IsString } from 'class-validator';

export class UpdatePreferencesDto {
  @IsOptional()
  @IsString()
  favorite_sport?: string;

  @IsOptional()
  @IsString()
  favorite_team?: string;
}
