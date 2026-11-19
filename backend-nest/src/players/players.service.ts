import { Injectable, NotFoundException, BadRequestException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Player } from '../database/entities/player.entity';
import { Match } from '../database/entities/match.entity';
import { CreatePlayerDto, UpdatePlayerScoreDto } from './dto/create-player.dto';
import { BatchCreatePlayersDto } from './dto/batch-players.dto';

@Injectable()
export class PlayersService {
  constructor(
    @InjectRepository(Player)
    private readonly playerRepository: Repository<Player>,
    @InjectRepository(Match)
    private readonly matchRepository: Repository<Match>,
  ) {}

  async getPlayersByMatch(matchId: number, team?: string) {
    const match = await this.matchRepository.findOne({ where: { id: matchId } });
    if (!match) {
      throw new NotFoundException(`Match ${matchId} not found`);
    }

    const where: any = { match_id: matchId };
    if (team) {
      where.team = team;
    }

    const players = await this.playerRepository.find({
      where,
      order: { id: 'ASC' },
    });

    return {
      success: true,
      match_id: matchId,
      team1: match.team1,
      team2: match.team2,
      players,
    };
  }

  async addPlayer(dto: CreatePlayerDto) {
    const match = await this.matchRepository.findOne({
      where: { id: dto.match_id },
    });
    if (!match) {
      throw new NotFoundException(`Match with ID ${dto.match_id} not found`);
    }

    const player = this.playerRepository.create({
      match_id: dto.match_id,
      team: dto.team,
      name: dto.name,
      role: dto.role || 'Player',
      score: dto.score || 0,
      sport: dto.sport || match.sport,
    });

    const savedPlayer = await this.playerRepository.save(player);

    // If score > 0, update match score
    if (savedPlayer.score > 0) {
      if (dto.team === match.team1) {
        match.team1_score = (match.team1_score || 0) + savedPlayer.score;
        await this.matchRepository.save(match);
      } else if (dto.team === match.team2) {
        match.team2_score = (match.team2_score || 0) + savedPlayer.score;
        await this.matchRepository.save(match);
      }
    }

    return {
      success: true,
      message: 'Player added successfully',
      player: savedPlayer,
    };
  }

  async updatePlayerScore(id: number, dto: UpdatePlayerScoreDto) {
    const player = await this.playerRepository.findOne({ where: { id } });
    if (!player) {
      throw new NotFoundException(`Player with ID ${id} not found`);
    }

    const scoreDifference = dto.score - (player.score || 0);
    player.score = dto.score;
    await this.playerRepository.save(player);

    // If score difference is non-zero, adjust match total
    if (scoreDifference !== 0) {
      const match = await this.matchRepository.findOne({
        where: { id: player.match_id },
      });
      if (match) {
        if (player.team === match.team1) {
          match.team1_score = Math.max(0, (match.team1_score || 0) + scoreDifference);
          await this.matchRepository.save(match);
        } else if (player.team === match.team2) {
          match.team2_score = Math.max(0, (match.team2_score || 0) + scoreDifference);
          await this.matchRepository.save(match);
        }
      }
    }

    return {
      success: true,
      message: 'Player score updated successfully',
      player,
    };
  }

  async deletePlayer(id: number) {
    const player = await this.playerRepository.findOne({ where: { id } });
    if (!player) {
      throw new NotFoundException(`Player with ID ${id} not found`);
    }

    const scoreToDeduct = player.score || 0;
    const matchId = player.match_id;
    const team = player.team;

    await this.playerRepository.remove(player);

    // If deleted player had score, deduct from match total
    if (scoreToDeduct > 0) {
      const match = await this.matchRepository.findOne({
        where: { id: matchId },
      });
      if (match) {
        if (team === match.team1) {
          match.team1_score = Math.max(0, (match.team1_score || 0) - scoreToDeduct);
          await this.matchRepository.save(match);
        } else if (team === match.team2) {
          match.team2_score = Math.max(0, (match.team2_score || 0) - scoreToDeduct);
          await this.matchRepository.save(match);
        }
      }
    }

    return {
      success: true,
      message: 'Player deleted successfully',
    };
  }

  async batchCreatePlayers(dto: BatchCreatePlayersDto) {
    const match = await this.matchRepository.findOne({
      where: { id: dto.match_id },
    });
    if (!match) {
      throw new NotFoundException(`Match ${dto.match_id} not found`);
    }

    const createdPlayers: Player[] = [];

    // Team 1 players
    if (dto.team1?.players && Array.isArray(dto.team1.players)) {
      for (const p of dto.team1.players) {
        const player = this.playerRepository.create({
          match_id: match.id,
          team: dto.team1.name,
          name: p.name,
          role: p.role,
          score: p.score || 0,
          sport: dto.sport,
        });
        const saved = await this.playerRepository.save(player);
        createdPlayers.push(saved);
        if (saved.score > 0) {
          match.team1_score = (match.team1_score || 0) + saved.score;
        }
      }
    }

    // Team 2 players
    if (dto.team2?.players && Array.isArray(dto.team2.players)) {
      for (const p of dto.team2.players) {
        const player = this.playerRepository.create({
          match_id: match.id,
          team: dto.team2.name,
          name: p.name,
          role: p.role,
          score: p.score || 0,
          sport: dto.sport,
        });
        const saved = await this.playerRepository.save(player);
        createdPlayers.push(saved);
        if (saved.score > 0) {
          match.team2_score = (match.team2_score || 0) + saved.score;
        }
      }
    }

    await this.matchRepository.save(match);

    return {
      success: true,
      message: `${createdPlayers.length} players added successfully`,
      players: createdPlayers,
    };
  }
}
