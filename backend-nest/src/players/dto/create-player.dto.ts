import {
  IsNotEmpty,
  IsNumber,
  IsOptional,
  IsString,
} from 'class-validator';

export class CreatePlayerDto {
  @IsNumber()
  @IsNotEmpty({ message: 'Match ID is required' })
  match_id: number;

  @IsString()
  @IsNotEmpty({ message: 'Team is required' })
  team: string;

  @IsString()
  @IsNotEmpty({ message: 'Name is required' })
  name: string;

  @IsString()
  @IsNotEmpty({ message: 'Role is required' })
  role: string;

  @IsOptional()
  @IsNumber()
  score?: number;

  @IsOptional()
  @IsString()
  sport?: string;
}

export class UpdatePlayerScoreDto {
  @IsNumber()
  @IsNotEmpty({ message: 'Score is required' })
  score: number;
}
