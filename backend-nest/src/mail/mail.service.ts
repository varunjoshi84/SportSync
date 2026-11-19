import { Injectable, Logger } from '@nestjs/common';
import * as nodemailer from 'nodemailer';

@Injectable()
export class MailService {
  private readonly logger = new Logger(MailService.name);
  private transporter: nodemailer.Transporter;

  constructor() {
    this.transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST || 'smtp.gmail.com',
      port: parseInt(process.env.SMTP_PORT, 10) || 587,
      secure: false,
      auth: {
        user: process.env.SMTP_USER || 'joshivarun266@gmail.com',
        pass: process.env.SMTP_PASS || 'cvdy gcfj deal qwps',
      },
    });
  }

  async sendEmail(to: string, subject: string, html: string): Promise<boolean> {
    try {
      const from = process.env.SMTP_FROM || 'SportSync <noreply@sportsync.com>';
      await this.transporter.sendMail({
        from,
        to,
        subject,
        html,
      });
      return true;
    } catch (error) {
      this.logger.warn(`Could not dispatch email to ${to}: ${error.message}`);
      return false;
    }
  }

  async sendFeedbackEmail(
    name: string,
    email: string,
    subject: string,
    message: string,
  ): Promise<boolean> {
    const emailSubject = 'Thank you for your feedback - SportSync';
    const body = `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
        <h2 style="color: #dc2626;">Thank you for your valuable feedback!</h2>
        <p>Dear ${name},</p>
        <p>We have received your feedback regarding <strong>${subject}</strong> and appreciate you taking the time to share your thoughts with us.</p>
        <p>Your message:</p>
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 15px 0;">
          ${message}
        </div>
        <p>We will review your feedback and get back to you if necessary.</p>
        <p>Best regards,<br>The SportSync Team</p>
      </div>
    `;
    return this.sendEmail(email, emailSubject, body);
  }

  async sendSubscriptionEmail(email: string): Promise<boolean> {
    const subject = 'Welcome to SportSync Newsletter!';
    const body = `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
        <h2 style="color: #dc2626;">Thank you for subscribing!</h2>
        <p>Welcome to the SportSync newsletter!</p>
        <p>You have successfully subscribed to receive updates about:</p>
        <ul>
          <li>Latest match scores and updates</li>
          <li>Breaking sports news</li>
          <li>Special events and tournaments</li>
          <li>Exclusive content and analysis</li>
        </ul>
        <p>We're excited to keep you informed about all the latest happenings in the world of sports!</p>
        <p>Best regards,<br>The SportSync Team</p>
      </div>
    `;
    return this.sendEmail(email, subject, body);
  }
}
