import { TypeOrmModuleOptions } from '@nestjs/typeorm';
import { User } from '../database/entities/user.entity';
import { Match } from '../database/entities/match.entity';
import { Player } from '../database/entities/player.entity';
import { Favorite } from '../database/entities/favorite.entity';
import { News } from '../database/entities/news.entity';
import { Feedback } from '../database/entities/feedback.entity';
import { Subscription } from '../database/entities/subscription.entity';

export const getDatabaseConfig = (): TypeOrmModuleOptions => ({
  type: 'postgres',
  host: process.env.DB_HOST || 'localhost',
  port: parseInt(process.env.DB_PORT, 10) || 5432,
  username: process.env.DB_USERNAME || 'postgres',
  password: process.env.DB_PASSWORD || 'postgres',
  database: process.env.DB_DATABASE || 'sport_sync',
  entities: [
    User,
    Match,
    Player,
    Favorite,
    News,
    Feedback,
    Subscription,
  ],
  synchronize: true, // Auto create/update schema in development
  logging: false,
});
