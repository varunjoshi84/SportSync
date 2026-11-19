import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Feedback, FeedbackStatus } from '../database/entities/feedback.entity';
import { CreateFeedbackDto, UpdateFeedbackStatusDto } from './dto/create-feedback.dto';
import { MailService } from '../mail/mail.service';

@Injectable()
export class FeedbackService {
  constructor(
    @InjectRepository(Feedback)
    private readonly feedbackRepository: Repository<Feedback>,
    private readonly mailService: MailService,
  ) {}

  async submitFeedback(dto: CreateFeedbackDto) {
    const feedback = this.feedbackRepository.create({
      ...dto,
      status: FeedbackStatus.PENDING,
    });

    const saved = await this.feedbackRepository.save(feedback);

    // Send thank you confirmation email in background
    this.mailService.sendFeedbackEmail(dto.name, dto.email, dto.subject, dto.message).catch(() => {});

    return {
      success: true,
      message: 'Thank you for your feedback! We have received your message.',
      feedback: saved,
    };
  }

  async getAllFeedback() {
    return this.feedbackRepository.find({
      order: { created_at: 'DESC' },
    });
  }

  async updateStatus(id: number, dto: UpdateFeedbackStatusDto) {
    const feedback = await this.feedbackRepository.findOne({ where: { id } });
    if (!feedback) {
      throw new NotFoundException(`Feedback with ID ${id} not found`);
    }

    feedback.status = dto.status;
    const saved = await this.feedbackRepository.save(feedback);

    return {
      success: true,
      message: 'Feedback status updated successfully!',
      feedback: saved,
    };
  }

  async deleteFeedback(id: number) {
    const feedback = await this.feedbackRepository.findOne({ where: { id } });
    if (!feedback) {
      throw new NotFoundException(`Feedback with ID ${id} not found`);
    }

    await this.feedbackRepository.remove(feedback);

    return {
      success: true,
      message: 'Feedback deleted successfully!',
    };
  }
}
