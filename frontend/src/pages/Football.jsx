import React, { useState, useEffect } from 'react';
import { Search } from 'lucide-react';
import { MatchCard } from '../components/MatchCard';
import api from '../services/api';
import '../styles/matches.css';

export const Football = () => {
  const [filter, setFilter] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [matches, setMatches] = useState([]);
  const [liveCount, setLiveCount] = useState(0);
  const [loading, setLoading] = useState(true);

  const fetchMatches = async (statusFilter, search) => {
    try {
      setLoading(true);
      const params = new URLSearchParams();
      params.append('sport', 'football');
      if (statusFilter !== 'all') params.append('status', statusFilter);
      if (search && search.trim()) params.append('search', search.trim());

      const [matchesRes, liveRes] = await Promise.all([
        api.get(`/matches?${params.toString()}`),
        api.get('/matches?sport=football&status=live'),
      ]);

      setMatches(matchesRes.data || []);
      setLiveCount((liveRes.data || []).length);
    } catch (err) {
      console.error('Error fetching football matches:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const handler = setTimeout(() => {
      fetchMatches(filter, searchQuery);
    }, 250);
    return () => clearTimeout(handler);
  }, [filter, searchQuery]);

  return (
    <div className="container" style={{ padding: '40px 20px 80px' }}>
      <div className="matches-page-header">
        <h1 className="matches-page-title">Football</h1>
        <p className="matches-page-subtitle">
          Latest matches, live updates, and team rosters for football fans
        </p>
      </div>

      {/* Filter and Search Bar */}
      <div className="matches-controls-bar">
        <div className="status-filter-tabs" style={{ marginBottom: 0 }}>
          <button
            className={`status-tab-btn ${filter === 'all' ? 'active' : ''}`}
            onClick={() => setFilter('all')}
          >
            All
          </button>
          <button
            className={`status-tab-btn ${filter === 'live' ? 'active' : ''}`}
            onClick={() => setFilter('live')}
          >
            Live
            <span className="badge-count">{liveCount}</span>
          </button>
          <button
            className={`status-tab-btn ${filter === 'upcoming' ? 'active' : ''}`}
            onClick={() => setFilter('upcoming')}
          >
            Upcoming
          </button>
          <button
            className={`status-tab-btn ${filter === 'completed' ? 'active' : ''}`}
            onClick={() => setFilter('completed')}
          >
            Completed
          </button>
        </div>

        <div className="search-input-wrapper">
          <Search size={16} className="search-icon-adornment" />
          <input
            type="text"
            className="search-input-field"
            placeholder="Search teams or venues..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>
      </div>

      {/* Match Cards Grid */}
      {loading ? (
        <div style={{ textAlign: 'center', padding: '60px 0', color: 'var(--text-secondary)' }}>
          Loading football matches...
        </div>
      ) : matches.length > 0 ? (
        <div className="matches-grid">
          {matches.map((match) => (
            <MatchCard key={match.id} match={match} />
          ))}
        </div>
      ) : (
        <div className="empty-state">
          No {filter !== 'all' ? filter : ''} football matches at the moment.
        </div>
      )}
    </div>
  );
};
