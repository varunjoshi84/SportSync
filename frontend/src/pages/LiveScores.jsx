import React, { useState, useEffect } from 'react';
import { RefreshCw } from 'lucide-react';
import { MatchCard } from '../components/MatchCard';
import api from '../services/api';
import '../styles/matches.css';

export const LiveScores = () => {
  const [sportFilter, setSportFilter] = useState('all');
  const [matches, setMatches] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [lastRefreshed, setLastRefreshed] = useState(new Date());

  const fetchLiveScores = async (sport, isSilent = false) => {
    try {
      if (!isSilent) setLoading(true);
      else setRefreshing(true);

      const url = sport === 'all' ? '/matches/live' : `/matches/live?sport=${sport}`;
      const res = await api.get(url);
      setMatches(res.data || []);
      setLastRefreshed(new Date());
    } catch (err) {
      console.error('Error fetching live scores:', err);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchLiveScores(sportFilter);

    // Auto-refresh live scores every 15 seconds
    const interval = setInterval(() => {
      fetchLiveScores(sportFilter, true);
    }, 15000);

    return () => clearInterval(interval);
  }, [sportFilter]);

  return (
    <div className="container" style={{ padding: '40px 20px 80px' }}>
      <div className="section-header-row" style={{ marginBottom: '32px' }}>
        <div>
          <h1 className="matches-page-title" style={{ margin: 0 }}>All Live Scores</h1>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.85rem', marginTop: '4px' }}>
            Auto-refreshes every 15s · Last updated: {lastRefreshed.toLocaleTimeString()}
          </p>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
          <button
            className="match-card-icon-btn"
            onClick={() => fetchLiveScores(sportFilter, false)}
            title="Refresh Scores Now"
            style={{ padding: '8px 12px', gap: '6px' }}
          >
            <RefreshCw size={14} className={refreshing ? 'rotating-spinner' : ''} />
            <span style={{ fontSize: '0.85rem' }}>Refresh</span>
          </button>

          <div className="filter-btn-group">
            <button
              className={`filter-pill-btn ${sportFilter === 'all' ? 'active' : ''}`}
              onClick={() => setSportFilter('all')}
            >
              All
            </button>
            <button
              className={`filter-pill-btn ${sportFilter === 'football' ? 'active' : ''}`}
              onClick={() => setSportFilter('football')}
            >
              Football
            </button>
            <button
              className={`filter-pill-btn ${sportFilter === 'cricket' ? 'active' : ''}`}
              onClick={() => setSportFilter('cricket')}
            >
              Cricket
            </button>
          </div>
        </div>
      </div>

      {loading ? (
        <div style={{ textAlign: 'center', padding: '60px 0', color: 'var(--text-secondary)' }}>
          Loading live scores...
        </div>
      ) : matches.length > 0 ? (
        <div className="matches-grid">
          {matches.map((match) => (
            <MatchCard key={match.id} match={match} />
          ))}
        </div>
      ) : (
        <div className="empty-state">No live matches at the moment.</div>
      )}
    </div>
  );
};
