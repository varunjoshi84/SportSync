import { DataSource } from 'typeorm';
import * as bcrypt from 'bcrypt';
import * as dotenv from 'dotenv';
import { User, UserRole } from './entities/user.entity';
import { Match, MatchSport, MatchStatus } from './entities/match.entity';
import { Player } from './entities/player.entity';
import { Favorite } from './entities/favorite.entity';
import { News } from './entities/news.entity';
import { Feedback, FeedbackStatus } from './entities/feedback.entity';
import { Subscription } from './entities/subscription.entity';

dotenv.config();

const dataSource = new DataSource({
  type: 'postgres',
  host: process.env.DB_HOST || 'localhost',
  port: parseInt(process.env.DB_PORT, 10) || 5432,
  username: process.env.DB_USERNAME || 'postgres',
  password: process.env.DB_PASSWORD || 'postgres',
  database: process.env.DB_DATABASE || 'sport_sync',
  entities: [User, Match, Player, Favorite, News, Feedback, Subscription],
  synchronize: true,
});

async function seed() {
  await dataSource.initialize();
  console.log('Database connected for seeding...');

  const userRepo = dataSource.getRepository(User);
  const matchRepo = dataSource.getRepository(Match);
  const playerRepo = dataSource.getRepository(Player);
  const newsRepo = dataSource.getRepository(News);
  const feedbackRepo = dataSource.getRepository(Feedback);
  const subRepo = dataSource.getRepository(Subscription);

  // 1. Seed Users
  const adminPassword = await bcrypt.hash('admin123', 10);
  const userPassword = await bcrypt.hash('password123', 10);

  const initialUsers = [
    {
      username: 'admin',
      email: 'admin@sportsync.com',
      password: adminPassword,
      role: UserRole.ADMIN,
      account_type: UserRole.ADMIN,
      is_verified: true,
      favorite_sport: 'Football',
      favorite_team: 'Manchester United',
    },
    {
      username: 'varunpndey',
      email: 'varunpndey@outlook.com',
      password: adminPassword,
      role: UserRole.ADMIN,
      account_type: UserRole.ADMIN,
      is_verified: true,
      favorite_sport: 'Cricket',
      favorite_team: 'India',
    },
    {
      username: 'varunjoshi',
      email: 'varunjoshi@gmail.com',
      password: adminPassword,
      role: UserRole.ADMIN,
      account_type: UserRole.ADMIN,
      is_verified: true,
    },
    {
      username: 'testuser',
      email: 'test@example.com',
      password: userPassword,
      role: UserRole.USER,
      account_type: UserRole.USER,
      is_verified: true,
      favorite_sport: 'Cricket',
      favorite_team: 'India',
    },
    {
      username: 'john_doe',
      email: 'john@example.com',
      password: userPassword,
      role: UserRole.USER,
      account_type: UserRole.USER,
      is_verified: true,
      favorite_sport: 'Football',
      favorite_team: 'Real Madrid',
    },
  ];

  for (const u of initialUsers) {
    const exists = await userRepo.findOne({ where: { email: u.email } });
    if (!exists) {
      const user = userRepo.create(u);
      await userRepo.save(user);
    }
  }
  console.log('Users seeded.');

  // 2. Seed Matches
  const now = new Date();
  const past2h = new Date(now.getTime() - 2 * 3600 * 1000);
  const past1d = new Date(now.getTime() - 24 * 3600 * 1000);
  const future2d = new Date(now.getTime() + 48 * 3600 * 1000);
  const future5d = new Date(now.getTime() + 120 * 3600 * 1000);

  const initialMatches = [
    // Live Cricket
    {
      sport: MatchSport.CRICKET,
      team1: 'India',
      team2: 'Australia',
      team1_score: 185,
      team2_score: 142,
      team1_wickets: 4,
      team2_wickets: 6,
      team1_overs: 18.2,
      team2_overs: 16.0,
      venue: 'Melbourne Cricket Ground',
      location: 'Melbourne',
      status: MatchStatus.LIVE,
      match_time: past2h,
    },
    // Upcoming Cricket
    {
      sport: MatchSport.CRICKET,
      team1: 'England',
      team2: 'Pakistan',
      team1_score: 0,
      team2_score: 0,
      team1_wickets: 0,
      team2_wickets: 0,
      team1_overs: 0.0,
      team2_overs: 0.0,
      venue: "Lord's Cricket Ground",
      location: 'London',
      status: MatchStatus.UPCOMING,
      match_time: future2d,
    },
    // Completed Cricket
    {
      sport: MatchSport.CRICKET,
      team1: 'India',
      team2: 'England',
      team1_score: 244,
      team2_score: 243,
      team1_wickets: 6,
      team2_wickets: 10,
      team1_overs: 20.0,
      team2_overs: 19.3,
      venue: 'The Oval, London',
      location: 'London',
      status: MatchStatus.COMPLETED,
      winner: 'India',
      match_time: past1d,
    },
    {
      sport: MatchSport.CRICKET,
      team1: 'South Africa',
      team2: 'New Zealand',
      team1_score: 190,
      team2_score: 194,
      team1_wickets: 8,
      team2_wickets: 5,
      team1_overs: 20.0,
      team2_overs: 19.1,
      venue: 'Eden Park',
      location: 'Auckland',
      status: MatchStatus.COMPLETED,
      winner: 'New Zealand',
      match_time: past1d,
    },
    // Live Football
    {
      sport: MatchSport.FOOTBALL,
      team1: 'Manchester United',
      team2: 'Liverpool',
      team1_score: 2,
      team2_score: 1,
      team1_shots: 14,
      team2_shots: 11,
      team1_possession: 52,
      team2_possession: 48,
      venue: 'Old Trafford',
      location: 'Manchester',
      status: MatchStatus.LIVE,
      match_time: past2h,
    },
    {
      sport: MatchSport.FOOTBALL,
      team1: 'Chelsea',
      team2: 'Arsenal',
      team1_score: 1,
      team2_score: 1,
      team1_shots: 8,
      team2_shots: 9,
      team1_possession: 46,
      team2_possession: 54,
      venue: 'Stamford Bridge',
      location: 'London',
      status: MatchStatus.LIVE,
      match_time: past2h,
    },
    // Upcoming Football
    {
      sport: MatchSport.FOOTBALL,
      team1: 'Real Madrid',
      team2: 'Barcelona',
      team1_score: 0,
      team2_score: 0,
      venue: 'Santiago Bernabéu',
      location: 'Madrid',
      status: MatchStatus.UPCOMING,
      match_time: future2d,
    },
    {
      sport: MatchSport.FOOTBALL,
      team1: 'Bayern Munich',
      team2: 'Borussia Dortmund',
      team1_score: 0,
      team2_score: 0,
      venue: 'Allianz Arena',
      location: 'Munich',
      status: MatchStatus.UPCOMING,
      match_time: future5d,
    },
    // Completed Football
    {
      sport: MatchSport.FOOTBALL,
      team1: 'Bayern Munich',
      team2: 'Paris Saint-Germain',
      team1_score: 5,
      team2_score: 2,
      team1_shots: 19,
      team2_shots: 8,
      team1_possession: 61,
      team2_possession: 39,
      venue: 'Allianz Arena',
      location: 'Munich',
      status: MatchStatus.COMPLETED,
      winner: 'Bayern Munich',
      match_time: past1d,
    },
    {
      sport: MatchSport.FOOTBALL,
      team1: 'Argentina',
      team2: 'France',
      team1_score: 3,
      team2_score: 3,
      team1_shots: 12,
      team2_shots: 10,
      team1_possession: 54,
      team2_possession: 46,
      venue: 'Lusail Iconic Stadium',
      location: 'Qatar',
      status: MatchStatus.COMPLETED,
      winner: 'Draw',
      match_time: past1d,
    },
  ];

  const matchCount = await matchRepo.count();
  if (matchCount === 0) {
    for (const m of initialMatches) {
      const match = matchRepo.create(m);
      const saved = await matchRepo.save(match);

      // Seed sample players for this match
      if (saved.sport === MatchSport.CRICKET) {
        const p1 = playerRepo.create({
          match_id: saved.id,
          team: saved.team1,
          name: 'Rohit Sharma',
          role: 'Captain',
          score: 65,
          sport: 'cricket',
        });
        const p2 = playerRepo.create({
          match_id: saved.id,
          team: saved.team1,
          name: 'Virat Kohli',
          role: 'Batsman',
          score: 82,
          sport: 'cricket',
        });
        const p3 = playerRepo.create({
          match_id: saved.id,
          team: saved.team1,
          name: 'Jasprit Bumrah',
          role: 'Bowler',
          score: 12,
          sport: 'cricket',
        });
        const p4 = playerRepo.create({
          match_id: saved.id,
          team: saved.team2,
          name: 'Pat Cummins',
          role: 'Captain',
          score: 34,
          sport: 'cricket',
        });
        const p5 = playerRepo.create({
          match_id: saved.id,
          team: saved.team2,
          name: 'Travis Head',
          role: 'Batsman',
          score: 74,
          sport: 'cricket',
        });
        await playerRepo.save([p1, p2, p3, p4, p5]);
      } else {
        const p1 = playerRepo.create({
          match_id: saved.id,
          team: saved.team1,
          name: 'Marcus Rashford',
          role: 'Forward',
          score: 1,
          sport: 'football',
        });
        const p2 = playerRepo.create({
          match_id: saved.id,
          team: saved.team1,
          name: 'Bruno Fernandes',
          role: 'Captain',
          score: 1,
          sport: 'football',
        });
        const p3 = playerRepo.create({
          match_id: saved.id,
          team: saved.team1,
          name: 'Andre Onana',
          role: 'Goalkeeper',
          score: 0,
          sport: 'football',
        });
        const p4 = playerRepo.create({
          match_id: saved.id,
          team: saved.team2,
          name: 'Mohamed Salah',
          role: 'Forward',
          score: 1,
          sport: 'football',
        });
        const p5 = playerRepo.create({
          match_id: saved.id,
          team: saved.team2,
          name: 'Virgil van Dijk',
          role: 'Captain',
          score: 0,
          sport: 'football',
        });
        await playerRepo.save([p1, p2, p3, p4, p5]);
      }
    }
    console.log('Matches and Players seeded.');
  }

  // 3. Seed News
  const newsCount = await newsRepo.count();
  if (newsCount === 0) {
    const sampleNews = [
      {
        category: 'football',
        title: 'Champions League Quarter-Finals Draw Announced',
        content: "The UEFA Champions League quarter-finals draw has been completed, setting up some exciting matchups between Europe's top teams including powerhouse clashes and historic rivalries...",
      },
      {
        category: 'cricket',
        title: 'IPL 2024 Opening Ceremony Details Revealed',
        content: 'The Indian Premier League is set to begin with a spectacular opening ceremony featuring top performers and cricket legends from across the globe in a grand stadium extravaganza...',
      },
      {
        category: 'football',
        title: 'Premier League Title Race Heats Up',
        content: 'With crucial games remaining in the season, three elite clubs are separated by just two points in an intense race to championship glory and silverware...',
      },
      {
        category: 'cricket',
        title: 'World Cup 2024 Schedule Released',
        content: 'The ICC has officially announced the complete match fixtures and tournament stages for the upcoming World Cup, with matches spanning state-of-the-art international venues...',
      },
      {
        category: 'football',
        title: 'Transfer News: Star Striker Set for Record Move',
        content: "One of football's biggest names has reached a milestone agreement to complete a record-shattering transfer ahead of next season's European campaign...",
      },
    ];
    await newsRepo.save(sampleNews);
    console.log('News seeded.');
  }

  // 4. Seed Feedback
  const feedbackCount = await feedbackRepo.count();
  if (feedbackCount === 0) {
    const sampleFeedback = [
      {
        name: 'Varun Joshi',
        email: 'varunpndey@outlook.com',
        subject: 'Great Live Scoreboard Updates',
        message: 'The live scoring and ball-by-ball updates are extremely fast and accurate! Love the clean interface.',
        status: FeedbackStatus.READ,
      },
      {
        name: 'Abhay Lal',
        email: 'abhaylal122@gmail.com',
        subject: 'Cricket Statistics Inquiry',
        message: 'Would love to see more historical cricket team records integrated into the match summary!',
        status: FeedbackStatus.PENDING,
      },
    ];
    await feedbackRepo.save(sampleFeedback);
    console.log('Feedback seeded.');
  }

  // 5. Seed Subscriptions
  const subCount = await subRepo.count();
  if (subCount === 0) {
    const sampleSubs = [
      { email: 'varunpndey@outlook.com' },
      { email: 'varun.raj.joshi@gmail.com' },
      { email: 'abhaylal122@gmail.com' },
    ];
    await subRepo.save(sampleSubs);
    console.log('Subscriptions seeded.');
  }

  console.log('All seeding completed successfully!');
  await dataSource.destroy();
}

seed().catch((err) => {
  console.error('Seeding failed:', err);
  process.exit(1);
});
