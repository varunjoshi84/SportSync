import React, { useState, useEffect } from 'react';
import { MatchCard } from '../components/MatchCard';
import api from '../services/api';
import '../styles/matches.css';

export const FavoriteMatches = () => {
  const [sportFilter, setSportFilter] = useState('all');
  const [statusFilter, setStatusFilter] = useState('all');
  const [favorites, setFavorites] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchFavorites = async () => {
    try {
      setLoading(true);
      let query = [];
      if (sportFilter !== 'all') query.push(`sport=${sportFilter}`);
      if (statusFilter !== 'all') query.push(`status=${statusFilter}`);
      const qs = query.length > 0 ? `?${query.join('&')}` : '';

      const res = await api.get(`/favorites${qs}`);
      setFavorites(res.data || []);
    } catch (err) {
      console.error('Error fetching favorites:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFavorites();
  }, [sportFilter, statusFilter]);

  const handleFavoriteChange = (matchId, isFav) => {
    if (!isFav) {
      // Remove match from current list
      setFavorites((prev) => prev.filter((m) => m.id !== matchId));
    }
  };

  return (
    <div className="container" style={{ padding: '40px 20px 80px' }}>
      <div className="matches-page-header">
        <h1 className="matches-page-title">My Favorite Matches</h1>
      </div>

      {/* Dual Filter Controls: Sport and Status */}
      <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: '20px', marginBottom: '32px' }}>
        {/* Sport filters */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <span style={{ color: 'var(--text-secondary)', fontSize: '0.9rem', fontWeight: 600 }}>Sport:</span>
          <button
            className={`filter-pill-btn ${sportFilter === 'all' ? 'active' : ''}`}
            onClick={() => setSportFilter('all')}
          >
            All Sports
          </button>
          <button
            className={`filter-pill-btn ${sportFilter === 'cricket' ? 'active' : ''}`}
            onClick={() => setSportFilter('cricket')}
          >
            Cricket
          </button>
          <button
            className={`filter-pill-btn ${sportFilter === 'football' ? 'active' : ''}`}
            onClick={() => setSportFilter('football')}
          >
            Football
          </button>
        </div>

        {/* Status filters */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <span style={{ color: 'var(--text-secondary)', fontSize: '0.9rem', fontWeight: 600 }}>Status:</span>
          <button
            className={`filter-pill-btn ${statusFilter === 'all' ? 'active' : ''}`}
            onClick={() => setStatusFilter('all')}
          >
            All Status
          </button>
          <button
            className={`filter-pill-btn ${statusFilter === 'live' ? 'active' : ''}`}
            onClick={() => setStatusFilter('live')}
          >
            Live
          </button>
          <button
            className={`filter-pill-btn ${statusFilter === 'upcoming' ? 'active' : ''}`}
            onClick={() => setStatusFilter('upcoming')}
          >
            Upcoming
          </button>
          <button
            className={`filter-pill-btn ${statusFilter === 'completed' ? 'active' : ''}`}
            onClick={() => setStatusFilter('completed')}
          >
            Completed
          </button>
        </div>
      </div>

      {/* Grid */}
      {loading ? (
        <div style={{ textAlign: 'center', padding: '60px 0', color: 'var(--text-secondary)' }}>
          Loading favorite matches...
        </div>
      ) : favorites.length > 0 ? (
        <div className="matches-grid">
          {favorites.map((match) => (
            <MatchCard
              key={match.id}
              match={match}
              onFavoriteChange={handleFavoriteChange}
            />
          ))}
        </div>
      ) : (
        <div className="empty-state">
          No favorite matches found with the selected filters.
        </div>
      )}
    </div>
  );
};
