import {
  Injectable,
  NotFoundException,
  BadRequestException,
} from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import * as bcrypt from 'bcrypt';
import { User } from '../database/entities/user.entity';
import { UpdatePreferencesDto } from './dto/update-preferences.dto';
import { ChangePasswordDto } from './dto/change-password.dto';
import { DeleteAccountDto } from './dto/delete-account.dto';

@Injectable()
export class UsersService {
  constructor(
    @InjectRepository(User)
    private readonly userRepository: Repository<User>,
  ) {}

  async getUserById(id: number) {
    const user = await this.userRepository.findOne({ where: { id } });
    if (!user) {
      throw new NotFoundException('User not found');
    }
    const { password, ...sanitized } = user;
    return sanitized;
  }

  async updatePreferences(userId: number, dto: UpdatePreferencesDto) {
    const user = await this.userRepository.findOne({ where: { id: userId } });
    if (!user) {
      throw new NotFoundException('User not found');
    }

    if (dto.favorite_sport !== undefined) {
      user.favorite_sport = dto.favorite_sport;
    }
    if (dto.favorite_team !== undefined) {
      user.favorite_team = dto.favorite_team;
    }

    await this.userRepository.save(user);

    const { password, ...sanitized } = user;
    return {
      success: true,
      message: 'Profile updated successfully!',
      user: sanitized,
    };
  }

  async changePassword(userId: number, dto: ChangePasswordDto) {
    const { current_password, new_password, confirm_new_password } = dto;

    if (new_password !== confirm_new_password) {
      throw new BadRequestException('Passwords do not match!');
    }

    const user = await this.userRepository.findOne({ where: { id: userId } });
    if (!user) {
      throw new NotFoundException('User not found');
    }

    const isMatch = await bcrypt.compare(current_password, user.password);
    if (!isMatch) {
      throw new BadRequestException('Current password is incorrect.');
    }

    user.password = await bcrypt.hash(new_password, 10);
    await this.userRepository.save(user);

    return {
      success: true,
      message: 'Password changed successfully!',
    };
  }

  async deleteAccount(userId: number, dto: DeleteAccountDto) {
    const user = await this.userRepository.findOne({ where: { id: userId } });
    if (!user) {
      throw new NotFoundException('User not found');
    }

    const isMatch = await bcrypt.compare(dto.password, user.password);
    if (!isMatch) {
      throw new BadRequestException(
        'Failed to delete account. Please check your password and try again.',
      );
    }

    await this.userRepository.remove(user);

    return {
      success: true,
      message: 'Account permanently deleted.',
    };
  }
}
