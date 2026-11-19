import {
  IsEnum,
  IsNotEmpty,
  IsOptional,
  IsString,
  IsNumber,
} from 'class-validator';
import { MatchSport, MatchStatus } from '../../database/entities/match.entity';

export class CreateMatchDto {
  @IsEnum(MatchSport, { message: 'Sport must be cricket or football' })
  sport: MatchSport;

  @IsString()
  @IsNotEmpty({ message: 'Team 1 is required' })
  team1: string;

  @IsString()
  @IsNotEmpty({ message: 'Team 2 is required' })
  team2: string;

  @IsString()
  @IsNotEmpty({ message: 'Venue is required' })
  venue: string;

  @IsOptional()
  @IsString()
  location?: string;

  @IsNotEmpty({ message: 'Match time is required' })
  match_time: string | Date;

  @IsOptional()
  @IsEnum(MatchStatus)
  status?: MatchStatus;

  @IsOptional()
  @IsNumber()
  team1_score?: number;

  @IsOptional()
  @IsNumber()
  team2_score?: number;
}
