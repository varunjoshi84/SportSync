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
import { MatchesService } from './matches.service';
import { MatchSport, MatchStatus } from '../database/entities/match.entity';
import { CreateMatchDto } from './dto/create-match.dto';
import { UpdateMatchDto } from './dto/update-match.dto';
import { JwtAuthGuard, OptionalJwtAuthGuard } from '../auth/jwt-auth.guard';
import { RolesGuard } from '../auth/roles.guard';
import { Roles } from '../auth/roles.decorator';
import { UserRole } from '../database/entities/user.entity';
import { CurrentUser } from '../auth/current-user.decorator';

@Controller('matches')
export class MatchesController {
  constructor(private readonly matchesService: MatchesService) {}

  @UseGuards(OptionalJwtAuthGuard)
  @Get()
  async getMatches(
    @Query('sport') sport?: MatchSport,
    @Query('status') status?: MatchStatus,
    @Query('search') search?: string,
    @CurrentUser() user?: any,
  ) {
    return this.matchesService.getMatches(sport, status, search, user?.id);
  }

  @UseGuards(OptionalJwtAuthGuard)
  @Get('live')
  async getLiveMatches(
    @Query('sport') sport?: MatchSport,
    @CurrentUser() user?: any,
  ) {
    return this.matchesService.getLiveMatches(sport, user?.id);
  }

  @UseGuards(OptionalJwtAuthGuard)
  @Get(':id')
  async getMatchById(
    @Param('id', ParseIntPipe) id: number,
    @CurrentUser() user?: any,
  ) {
    return this.matchesService.getMatchById(id, user?.id);
  }

  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles(UserRole.ADMIN)
  @Post()
  async createMatch(@Body() dto: CreateMatchDto) {
    return this.matchesService.createMatch(dto);
  }

  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles(UserRole.ADMIN)
  @Put(':id')
  async updateMatch(
    @Param('id', ParseIntPipe) id: number,
    @Body() dto: UpdateMatchDto,
  ) {
    return this.matchesService.updateMatch(id, dto);
  }

  @UseGuards(JwtAuthGuard, RolesGuard)
  @Roles(UserRole.ADMIN)
  @Delete(':id')
  async deleteMatch(@Param('id', ParseIntPipe) id: number) {
    return this.matchesService.deleteMatch(id);
  }
}
