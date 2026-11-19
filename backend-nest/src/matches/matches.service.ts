import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, Like } from 'typeorm';
import { Match, MatchSport, MatchStatus } from '../database/entities/match.entity';
import { Favorite } from '../database/entities/favorite.entity';
import { CreateMatchDto } from './dto/create-match.dto';
import { UpdateMatchDto } from './dto/update-match.dto';

@Injectable()
export class MatchesService {
  constructor(
    @InjectRepository(Match)
    private readonly matchRepository: Repository<Match>,
    @InjectRepository(Favorite)
    private readonly favoriteRepository: Repository<Favorite>,
  ) {}

  determineWinner(team1Score: number, team2Score: number, team1: string, team2: string): string | null {
    if (team1Score > team2Score) {
      return team1;
    } else if (team2Score > team1Score) {
      return team2;
    } else if (team1Score === team2Score) {
      return 'Draw';
    }
    return null;
  }

  async getMatches(
    sport?: MatchSport,
    status?: MatchStatus,
    search?: string,
    userId?: number,
  ) {
    const query = this.matchRepository.createQueryBuilder('match')
      .leftJoinAndSelect('match.players', 'players')
      .orderBy('match.match_time', 'DESC');

    if (sport) {
      query.andWhere('match.sport = :sport', { sport });
    }

    if (status) {
      query.andWhere('match.status = :status', { status });
    }

    if (search) {
      query.andWhere(
        '(LOWER(match.team1) LIKE :search OR LOWER(match.team2) LIKE :search OR LOWER(match.venue) LIKE :search)',
        { search: `%${search.toLowerCase()}%` },
      );
    }

    const matches = await query.getMany();

    // Attach is_favorite flag if user is logged in
    if (userId) {
      const userFavorites = await this.favoriteRepository.find({
        where: { user_id: userId },
      });
      const favoriteMatchIds = new Set(userFavorites.map((f) => f.match_id));

      return matches.map((m) => ({
        ...m,
        is_favorite: favoriteMatchIds.has(m.id),
      }));
    }

    return matches.map((m) => ({
      ...m,
      is_favorite: false,
    }));
  }

  async getLiveMatches(sport?: MatchSport, userId?: number) {
    const query = this.matchRepository.createQueryBuilder('match')
      .leftJoinAndSelect('match.players', 'players')
      .where('match.status = :status', { status: MatchStatus.LIVE })
      .orderBy('match.match_time', 'DESC');

    if (sport) {
      query.andWhere('match.sport = :sport', { sport });
    }

    const matches = await query.getMany();

    if (userId) {
      const userFavorites = await this.favoriteRepository.find({
        where: { user_id: userId },
      });
      const favoriteMatchIds = new Set(userFavorites.map((f) => f.match_id));

      return matches.map((m) => ({
        ...m,
        is_favorite: favoriteMatchIds.has(m.id),
      }));
    }

    return matches.map((m) => ({
      ...m,
      is_favorite: false,
    }));
  }

  async getMatchById(id: number, userId?: number) {
    const match = await this.matchRepository.findOne({
      where: { id },
      relations: ['players'],
    });

    if (!match) {
      throw new NotFoundException(`Match with ID ${id} not found`);
    }

    let isFavorite = false;
    if (userId) {
      const fav = await this.favoriteRepository.findOne({
        where: { user_id: userId, match_id: id },
      });
      isFavorite = !!fav;
    }

    // Split players into team1 and team2
    const team1Players = match.players.filter((p) => p.team === match.team1);
    const team2Players = match.players.filter((p) => p.team === match.team2);

    return {
      ...match,
      is_favorite: isFavorite,
      team1_players: team1Players,
      team2_players: team2Players,
    };
  }

  async createMatch(dto: CreateMatchDto) {
    const match = this.matchRepository.create({
      ...dto,
      match_time: new Date(dto.match_time),
      status: dto.status || MatchStatus.UPCOMING,
      team1_score: dto.team1_score || 0,
      team2_score: dto.team2_score || 0,
    });

    if (match.status === MatchStatus.COMPLETED) {
      match.winner = this.determineWinner(
        match.team1_score,
        match.team2_score,
        match.team1,
        match.team2,
      );
    }

    const saved = await this.matchRepository.save(match);
    return {
      success: true,
      message: 'Match added successfully!',
      match: saved,
    };
  }

  async updateMatch(id: number, dto: UpdateMatchDto) {
    const match = await this.matchRepository.findOne({ where: { id } });
    if (!match) {
      throw new NotFoundException(`Match with ID ${id} not found`);
    }

    // Merge non-undefined values
    Object.keys(dto).forEach((key) => {
      if (dto[key] !== undefined && dto[key] !== null) {
        if (key === 'match_time') {
          match.match_time = new Date(dto[key]);
        } else {
          match[key] = dto[key];
        }
      }
    });

    // Auto calculate winner if status is completed
    if (match.status === MatchStatus.COMPLETED) {
      match.winner = this.determineWinner(
        match.team1_score,
        match.team2_score,
        match.team1,
        match.team2,
      );
    }

    const saved = await this.matchRepository.save(match);
    return {
      success: true,
      message: 'Match updated successfully!',
      match: saved,
    };
  }

  async deleteMatch(id: number) {
    const match = await this.matchRepository.findOne({ where: { id } });
    if (!match) {
      throw new NotFoundException(`Match with ID ${id} not found`);
    }

    await this.matchRepository.remove(match);
    return {
      success: true,
      message: 'Match deleted successfully!',
    };
  }
}
