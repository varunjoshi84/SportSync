import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { NewsCarousel } from '../components/NewsCarousel';
import { MatchCard } from '../components/MatchCard';
import api from '../services/api';
import '../styles/home.css';
import '../styles/matches.css';

export const Home = () => {
  const [liveMatches, setLiveMatches] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchLiveMatches = async () => {
      try {
        const res = await api.get('/matches/live');
        setLiveMatches(res.data || []);
      } catch (err) {
        console.error('Error fetching live matches:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchLiveMatches();
  }, []);

  return (
    <div className="home-page">
      {/* Hero Section */}
      <section className="hero-section">
        <h1 className="hero-title">
          Your Live <span>Sports Hub</span>
        </h1>
        <p className="hero-subtitle">
          Real-time scores, match updates, and personalized notifications for all your favorite sports in one place.
        </p>
        <Link to="/live-scores" className="hero-cta-btn">
          View Live Scores &rarr;
        </Link>
      </section>

      <div className="container">
        {/* Latest Sports News Section */}
        <NewsCarousel />

        {/* Live Matches Section */}
        <section className="home-section-container">
          <div className="section-header-row">
            <h2 className="section-title">Live Matches</h2>
            <div className="filter-btn-group">
              <Link to="/football" className="filter-pill-btn">
                Football
              </Link>
              <Link to="/cricket" className="filter-pill-btn">
                Cricket
              </Link>
            </div>
          </div>

          {loading ? (
            <div style={{ textAlign: 'center', padding: '40px 0', color: 'var(--text-secondary)' }}>
              Loading live matches...
            </div>
          ) : liveMatches.length > 0 ? (
            <div className="matches-grid">
              {liveMatches.map((match) => (
                <MatchCard key={match.id} match={match} />
              ))}
            </div>
          ) : (
            <div className="empty-state">No live matches at the moment.</div>
          )}
        </section>
      </div>
    </div>
  );
};
