import {
  Controller,
  Get,
  Post,
  Body,
  Query,
  UseGuards,
} from '@nestjs/common';
import { FavoritesService } from './favorites.service';
import { ToggleFavoriteDto } from './dto/toggle-favorite.dto';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';
import { CurrentUser } from '../auth/current-user.decorator';
import { MatchSport, MatchStatus } from '../database/entities/match.entity';

@UseGuards(JwtAuthGuard)
@Controller('favorites')
export class FavoritesController {
  constructor(private readonly favoritesService: FavoritesService) {}

  @Post('toggle')
  async toggleFavorite(
    @CurrentUser() user: any,
    @Body() dto: ToggleFavoriteDto,
  ) {
    return this.favoritesService.toggleFavorite(user.id, dto);
  }

  @Get()
  async getFavorites(
    @CurrentUser() user: any,
    @Query('sport') sport?: MatchSport,
    @Query('status') status?: MatchStatus,
  ) {
    return this.favoritesService.getFavoriteMatches(user.id, sport, status);
  }
}
