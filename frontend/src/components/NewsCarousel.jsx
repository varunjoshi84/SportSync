import React, { useState, useEffect, useRef } from 'react';
import api from '../services/api';
import '../styles/home.css';

export const NewsCarousel = () => {
  const [news, setNews] = useState([]);
  const [activeCategory, setActiveCategory] = useState('all');
  const [currentSlide, setCurrentSlide] = useState(0);
  const [loading, setLoading] = useState(true);
  const timerRef = useRef(null);

  const fetchNews = async (category) => {
    try {
      setLoading(true);
      const url = category === 'all' ? '/news' : `/news?sport=${category}`;
      const res = await api.get(url);
      setNews(res.data || []);
      setCurrentSlide(0);
    } catch (err) {
      console.error('Error fetching news:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchNews(activeCategory);
  }, [activeCategory]);

  // Carousel timer
  useEffect(() => {
    if (news.length > 1) {
      timerRef.current = setInterval(() => {
        setCurrentSlide((prev) => (prev + 1) % news.length);
      }, 3000);
    }

    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [news]);

  const handleCategoryChange = (category) => {
    setActiveCategory(category);
  };

  const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  return (
    <div className="home-section-container">
      <div className="news-card-wrapper">
        <div className="section-header-row">
          <h2 className="section-title">Latest Sports News</h2>
          <div className="filter-btn-group">
            <button
              className={`filter-pill-btn ${activeCategory === 'all' ? 'active' : ''}`}
              onClick={() => handleCategoryChange('all')}
            >
              All
            </button>
            <button
              className={`filter-pill-btn ${activeCategory === 'football' ? 'active' : ''}`}
              onClick={() => handleCategoryChange('football')}
            >
              Football
            </button>
            <button
              className={`filter-pill-btn ${activeCategory === 'cricket' ? 'active' : ''}`}
              onClick={() => handleCategoryChange('cricket')}
            >
              Cricket
            </button>
          </div>
        </div>

        <div className="news-carousel-content">
          {loading ? (
            <div style={{ padding: '40px 0', textAlign: 'center', color: 'var(--text-secondary)' }}>
              Loading news...
            </div>
          ) : news.length > 0 ? (
            news.map((item, index) => (
              <div
                key={item.id}
                className={`carousel-slide ${index === currentSlide ? 'active' : ''}`}
              >
                <h3 className="carousel-slide-title">{item.title}</h3>
                <p className="carousel-slide-body">
                  {item.content.length > 200 ? `${item.content.substring(0, 200)}...` : item.content}
                </p>
                <div className="carousel-slide-meta">
                  <span>{formatDate(item.created_at)}</span>
                  <span className="category-tag">{item.category}</span>
                </div>
              </div>
            ))
          ) : (
            <div style={{ textAlign: 'center', padding: '40px 0' }}>
              <p style={{ color: 'var(--text-secondary)', fontSize: '1.1rem', marginBottom: '6px' }}>
                No {activeCategory !== 'all' ? activeCategory : 'sports'} news available at the moment
              </p>
              <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>Please check back later</p>
            </div>
          )}
        </div>

        {news.length > 1 && (
          <div className="carousel-indicators">
            {news.map((_, idx) => (
              <div
                key={idx}
                className={`indicator-dot ${idx === currentSlide ? 'active' : ''}`}
                onClick={() => setCurrentSlide(idx)}
              />
            ))}
          </div>
        )}
      </div>
    </div>
  );
};
