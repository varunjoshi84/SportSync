import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { News } from '../database/entities/news.entity';
import { CreateNewsDto, UpdateNewsDto } from './dto/create-news.dto';

@Injectable()
export class NewsService {
  constructor(
    @InjectRepository(News)
    private readonly newsRepository: Repository<News>,
  ) {}

  async getLatestNews(sport?: string) {
    const query = this.newsRepository.createQueryBuilder('news')
      .orderBy('news.created_at', 'DESC');

    if (sport && sport !== 'all') {
      query.where('LOWER(news.category) = LOWER(:sport)', { sport });
    }

    return query.take(10).getMany();
  }

  async getAllNews() {
    return this.newsRepository.find({
      order: { created_at: 'DESC' },
    });
  }

  async getNewsById(id: number) {
    const item = await this.newsRepository.findOne({ where: { id } });
    if (!item) {
      throw new NotFoundException(`News article with ID ${id} not found`);
    }
    return item;
  }

  async createNews(dto: CreateNewsDto) {
    const news = this.newsRepository.create(dto);
    const saved = await this.newsRepository.save(news);
    return {
      success: true,
      message: 'News article added successfully!',
      news: saved,
    };
  }

  async updateNews(id: number, dto: UpdateNewsDto) {
    const news = await this.newsRepository.findOne({ where: { id } });
    if (!news) {
      throw new NotFoundException(`News article with ID ${id} not found`);
    }

    Object.keys(dto).forEach((key) => {
      if (dto[key] !== undefined && dto[key] !== null) {
        news[key] = dto[key];
      }
    });

    const saved = await this.newsRepository.save(news);
    return {
      success: true,
      message: 'News article updated successfully!',
      news: saved,
    };
  }

  async deleteNews(id: number) {
    const news = await this.newsRepository.findOne({ where: { id } });
    if (!news) {
      throw new NotFoundException(`News article with ID ${id} not found`);
    }

    await this.newsRepository.remove(news);
    return {
      success: true,
      message: 'News article deleted successfully!',
    };
  }
}
