import { IsEmail, IsEnum, IsNotEmpty, IsString } from 'class-validator';
import { FeedbackStatus } from '../../database/entities/feedback.entity';

export class CreateFeedbackDto {
  @IsString()
  @IsNotEmpty({ message: 'Name is required' })
  name: string;

  @IsEmail({}, { message: 'Please enter a valid email address' })
  @IsNotEmpty({ message: 'Email is required' })
  email: string;

  @IsString()
  @IsNotEmpty({ message: 'Subject is required' })
  subject: string;

  @IsString()
  @IsNotEmpty({ message: 'Message is required' })
  message: string;
}

export class UpdateFeedbackStatusDto {
  @IsEnum(FeedbackStatus, { message: 'Status must be pending, read, or responded' })
  status: FeedbackStatus;
}
