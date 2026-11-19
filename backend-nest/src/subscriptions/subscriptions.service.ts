import { Injectable, ConflictException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Subscription } from '../database/entities/subscription.entity';
import { CreateSubscriptionDto } from './dto/create-subscription.dto';
import { MailService } from '../mail/mail.service';

@Injectable()
export class SubscriptionsService {
  constructor(
    @InjectRepository(Subscription)
    private readonly subscriptionRepository: Repository<Subscription>,
    private readonly mailService: MailService,
  ) {}

  async subscribe(dto: CreateSubscriptionDto) {
    const existing = await this.subscriptionRepository.findOne({
      where: { email: dto.email },
    });

    if (existing) {
      throw new ConflictException('This email is already subscribed');
    }

    const sub = this.subscriptionRepository.create(dto);
    await this.subscriptionRepository.save(sub);

    // Send welcome email in background
    this.mailService.sendSubscriptionEmail(dto.email).catch(() => {});

    return {
      success: true,
      message: 'Thank you for subscribing! We have sent a confirmation email.',
    };
  }

  async getAllSubscriptions() {
    return this.subscriptionRepository.find({
      order: { created_at: 'DESC' },
    });
  }
}
