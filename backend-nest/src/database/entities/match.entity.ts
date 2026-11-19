import {
  Entity,
  PrimaryGeneratedColumn,
  Column,
  CreateDateColumn,
  UpdateDateColumn,
  OneToMany,
} from 'typeorm';
import { Player } from './player.entity';
import { Favorite } from './favorite.entity';

export enum MatchSport {
  CRICKET = 'cricket',
  FOOTBALL = 'football',
}

export enum MatchStatus {
  UPCOMING = 'upcoming',
  LIVE = 'live',
  COMPLETED = 'completed',
}

@Entity('matches')
export class Match {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({
    type: 'enum',
    enum: MatchSport,
  })
  sport: MatchSport;

  @Column({ length: 100 })
  team1: string;

  @Column({ length: 100 })
  team2: string;

  @Column({ type: 'int', default: 0 })
  team1_score: number;

  @Column({ type: 'int', default: 0 })
  team2_score: number;

  @Column({
    type: 'enum',
    enum: MatchStatus,
    default: MatchStatus.UPCOMING,
  })
  status: MatchStatus;

  @Column({ length: 200, nullable: true })
  venue: string;

  @Column({ length: 100, nullable: true })
  location: string;

  @Column({ type: 'timestamp', nullable: true })
  match_time: Date;

  @Column({ length: 100, nullable: true })
  winner: string;

  // Cricket specific fields
  @Column({ type: 'int', default: 0 })
  team1_wickets: number;

  @Column({ type: 'int', default: 0 })
  team2_wickets: number;

  @Column({ type: 'decimal', precision: 5, scale: 1, default: 0.0 })
  team1_overs: number;

  @Column({ type: 'decimal', precision: 5, scale: 1, default: 0.0 })
  team2_overs: number;

  // Football specific fields
  @Column({ type: 'int', default: 0 })
  team1_shots: number;

  @Column({ type: 'int', default: 0 })
  team2_shots: number;

  @Column({ type: 'int', default: 50 })
  team1_possession: number;

  @Column({ type: 'int', default: 50 })
  team2_possession: number;

  @CreateDateColumn({ type: 'timestamp' })
  created_at: Date;

  @UpdateDateColumn({ type: 'timestamp' })
  updated_at: Date;

  @OneToMany(() => Player, (player) => player.match, { cascade: true })
  players: Player[];

  @OneToMany(() => Favorite, (favorite) => favorite.match)
  favorites: Favorite[];
}
