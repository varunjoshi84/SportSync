import {
  Entity,
  PrimaryGeneratedColumn,
  Column,
  CreateDateColumn,
  ManyToOne,
  JoinColumn,
} from 'typeorm';
import { Match } from './match.entity';

@Entity('players')
export class Player {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({ name: 'match_id' })
  match_id: number;

  @ManyToOne(() => Match, (match) => match.players, { onDelete: 'CASCADE' })
  @JoinColumn({ name: 'match_id' })
  match: Match;

  @Column({ length: 100 })
  team: string;

  @Column({ length: 100 })
  name: string;

  @Column({ length: 50 })
  role: string;

  @Column({ type: 'int', default: 0 })
  score: number;

  @Column({ length: 20 })
  sport: string;

  @CreateDateColumn({ type: 'timestamp' })
  created_at: Date;
}
