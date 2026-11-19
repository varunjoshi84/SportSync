import { IsArray, IsNotEmpty, IsNumber, IsOptional, IsString, ValidateNested } from 'class-validator';
import { Type } from 'class-transformer';

export class SinglePlayerItemDto {
  @IsString()
  @IsNotEmpty()
  name: string;

  @IsString()
  @IsNotEmpty()
  role: string;

  @IsOptional()
  @IsNumber()
  score?: number;
}

export class TeamPlayersItemDto {
  @IsString()
  @IsNotEmpty()
  name: string;

  @IsArray()
  @ValidateNested({ each: true })
  @Type(() => SinglePlayerItemDto)
  players: SinglePlayerItemDto[];
}

export class BatchCreatePlayersDto {
  @IsNumber()
  @IsNotEmpty()
  match_id: number;

  @IsString()
  @IsNotEmpty()
  sport: string;

  @ValidateNested()
  @Type(() => TeamPlayersItemDto)
  team1: TeamPlayersItemDto;

  @ValidateNested()
  @Type(() => TeamPlayersItemDto)
  team2: TeamPlayersItemDto;
}
