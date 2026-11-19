import {
  Controller,
  Get,
  Post,
  Put,
  Delete,
  Body,
  Param,
  Query,
  UseGuards,
  ParseIntPipe,
} from '@nestjs/common';
import { PlayersService } from './players.service';
import { CreatePlayerDto, UpdatePlayerScoreDto } from './dto/create-player.dto';
import { BatchCreatePlayersDto } from './dto/batch-players.dto';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';
import { RolesGuard } from '../auth/roles.guard';
import { Roles } from '../auth/roles.decorator';
import { UserRole } from '../database/entities/user.entity';

@Controller('players')
export class PlayersController {
  constructor(private readonly playersService: PlayersService) {}

  @Get()
  async getPlayers(
    @Query('match_id', ParseIntPipe) matchId: number,
    @Query('team') team?: string,
  ) {
    return this.playersService.getPlayersByMatch(matchId, team);
  }

  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles(UserRole.ADMIN)
  @Post()
  async addPlayer(@Body() dto: CreatePlayerDto) {
    return this.playersService.addPlayer(dto);
  }

  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles(UserRole.ADMIN)
  @Post('batch')
  async batchCreatePlayers(@Body() dto: BatchCreatePlayersDto) {
    return this.playersService.batchCreatePlayers(dto);
  }

  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles(UserRole.ADMIN)
  @Put(':id/score')
  async updateScore(
    @Param('id', ParseIntPipe) id: number,
    @Body() dto: UpdatePlayerScoreDto,
  ) {
    return this.playersService.updatePlayerScore(id, dto);
  }

  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles(UserRole.ADMIN)
  @Delete(':id')
  async deletePlayer(@Param('id', ParseIntPipe) id: number) {
    return this.playersService.deletePlayer(id);
  }
}
