import {
  Injectable,
  ConflictException,
  UnauthorizedException,
  InternalServerErrorException,
} from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { JwtService } from '@nestjs/jwt';
import * as bcrypt from 'bcrypt';
import * as crypto from 'crypto';
import { User, UserRole } from '../database/entities/user.entity';
import { RegisterDto } from './dto/register.dto';
import { LoginDto } from './dto/login.dto';

@Injectable()
export class AuthService {
  constructor(
    @InjectRepository(User)
    private readonly userRepository: Repository<User>,
    private readonly jwtService: JwtService,
  ) {}

  async register(registerDto: RegisterDto) {
    const { username, email, password } = registerDto;

    // Check if email already exists
    const existingEmail = await this.userRepository.findOne({
      where: { email },
    });
    if (existingEmail) {
      throw new ConflictException('Email already registered');
    }

    // Check if username already exists
    const existingUsername = await this.userRepository.findOne({
      where: { username },
    });
    if (existingUsername) {
      throw new ConflictException('Username is already taken');
    }

    try {
      const hashedPassword = await bcrypt.hash(password, 10);
      const user = this.userRepository.create({
        username,
        email,
        password: hashedPassword,
        role: UserRole.USER,
        account_type: UserRole.USER,
      });

      await this.userRepository.save(user);

      return {
        success: true,
        message: 'Registration successful! You can now login.',
      };
    } catch (error) {
      throw new InternalServerErrorException('Error registering user');
    }
  }

  async login(loginDto: LoginDto) {
    const { email, password } = loginDto;

    const user = await this.userRepository.findOne({
      where: { email },
    });

    if (!user) {
      throw new UnauthorizedException('Invalid email or password');
    }

    let passwordValid = false;

    // 1. Try standard bcrypt comparison
    try {
      passwordValid = await bcrypt.compare(password, user.password);
    } catch (e) {
      passwordValid = false;
    }

    // 2. Backward compatibility: check MD5 (as existed in legacy PHP)
    if (!passwordValid) {
      const md5Hash = crypto.createHash('md5').update(password).digest('hex');
      if (md5Hash === user.password) {
        passwordValid = true;
        // Upgrade password hash to bcrypt automatically
        user.password = await bcrypt.hash(password, 10);
        await this.userRepository.save(user);
      }
    }

    if (!passwordValid) {
      throw new UnauthorizedException('Invalid email or password');
    }

    const payload = {
      sub: user.id,
      email: user.email,
      username: user.username,
      role: user.role,
      account_type: user.account_type,
    };

    const token = this.jwtService.sign(payload);

    return {
      success: true,
      access_token: token,
      user: {
        id: user.id,
        username: user.username,
        email: user.email,
        role: user.role,
        account_type: user.account_type,
        favorite_sport: user.favorite_sport,
        favorite_team: user.favorite_team,
      },
    };
  }

  async validateUser(userId: number) {
    const user = await this.userRepository.findOne({
      where: { id: userId },
    });
    if (!user) {
      throw new UnauthorizedException('User not found');
    }
    const { password, ...sanitized } = user;
    return sanitized;
  }
}
