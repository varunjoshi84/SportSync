import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Favorite } from '../database/entities/favorite.entity';
import { Match, MatchSport, MatchStatus } from '../database/entities/match.entity';
import { ToggleFavoriteDto } from './dto/toggle-favorite.dto';

@Injectable()
export class FavoritesService {
  constructor(
    @InjectRepository(Favorite)
    private readonly favoriteRepository: Repository<Favorite>,
    @InjectRepository(Match)
    private readonly matchRepository: Repository<Match>,
  ) {}

  async toggleFavorite(userId: number, dto: ToggleFavoriteDto) {
    const { match_id } = dto;
    const match = await this.matchRepository.findOne({ where: { id: match_id } });
    if (!match) {
      throw new NotFoundException(`Match ${match_id} not found`);
    }

    const existing = await this.favoriteRepository.findOne({
      where: { user_id: userId, match_id },
    });

    if (existing) {
      await this.favoriteRepository.remove(existing);
      return {
        success: true,
        is_favorite: false,
        action: 'removed',
        message: 'Match removed from favorites',
      };
    } else {
      const fav = this.favoriteRepository.create({
        user_id: userId,
        match_id,
      });
      await this.favoriteRepository.save(fav);
      return {
        success: true,
        is_favorite: true,
        action: 'added',
        message: 'Match added to favorites',
      };
    }
  }

  async getFavoriteMatches(
    userId: number,
    sport?: MatchSport,
    status?: MatchStatus,
  ) {
    const query = this.favoriteRepository.createQueryBuilder('fav')
      .innerJoinAndSelect('fav.match', 'match')
      .leftJoinAndSelect('match.players', 'players')
      .where('fav.user_id = :userId', { userId })
      .orderBy('match.match_time', 'DESC');

    if (sport) {
      query.andWhere('match.sport = :sport', { sport });
    }

    if (status) {
      query.andWhere('match.status = :status', { status });
    }

    const favorites = await query.getMany();

    return favorites.map((fav) => ({
      ...fav.match,
      is_favorite: true,
    }));
  }

  async isMatchFavorited(userId: number, matchId: number): Promise<boolean> {
    const count = await this.favoriteRepository.count({
      where: { user_id: userId, match_id: matchId },
    });
    return count > 0;
  }
}
