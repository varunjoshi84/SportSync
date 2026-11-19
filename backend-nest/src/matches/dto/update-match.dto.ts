import {
  IsEnum,
  IsOptional,
  IsString,
  IsNumber,
} from 'class-validator';
import { MatchSport, MatchStatus } from '../../database/entities/match.entity';

export class UpdateMatchDto {
  @IsOptional()
  @IsEnum(MatchSport)
  sport?: MatchSport;

  @IsOptional()
  @IsString()
  team1?: string;

  @IsOptional()
  @IsString()
  team2?: string;

  @IsOptional()
  @IsString()
  venue?: string;

  @IsOptional()
  @IsString()
  location?: string;

  @IsOptional()
  match_time?: string | Date;

  @IsOptional()
  @IsEnum(MatchStatus)
  status?: MatchStatus;

  @IsOptional()
  @IsNumber()
  team1_score?: number;

  @IsOptional()
  @IsNumber()
  team2_score?: number;

  @IsOptional()
  @IsString()
  winner?: string;

  // Cricket specific
  @IsOptional()
  @IsNumber()
  team1_wickets?: number;

  @IsOptional()
  @IsNumber()
  team2_wickets?: number;

  @IsOptional()
  @IsNumber()
  team1_overs?: number;

  @IsOptional()
  @IsNumber()
  team2_overs?: number;

  // Football specific
  @IsOptional()
  @IsNumber()
  team1_shots?: number;

  @IsOptional()
  @IsNumber()
  team2_shots?: number;

  @IsOptional()
  @IsNumber()
  team1_possession?: number;

  @IsOptional()
  @IsNumber()
  team2_possession?: number;
}
