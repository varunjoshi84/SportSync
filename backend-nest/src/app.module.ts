import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { getDatabaseConfig } from './config/database.config';
import { AuthModule } from './auth/auth.module';
import { UsersModule } from './users/users.module';
import { MatchesModule } from './matches/matches.module';
import { PlayersModule } from './players/players.module';
import { FavoritesModule } from './favorites/favorites.module';
import { NewsModule } from './news/news.module';
import { FeedbackModule } from './feedback/feedback.module';
import { SubscriptionsModule } from './subscriptions/subscriptions.module';
import { MailModule } from './mail/mail.module';

@Module({
  imports: [
    ConfigModule.forRoot({
      isGlobal: true,
      envFilePath: '.env',
    }),
    TypeOrmModule.forRoot(getDatabaseConfig()),
    AuthModule,
    UsersModule,
    MatchesModule,
    PlayersModule,
    FavoritesModule,
    NewsModule,
    FeedbackModule,
    SubscriptionsModule,
    MailModule,
  ],
})
export class AppModule {}
