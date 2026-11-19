import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Heart, Share2, Check } from 'lucide-react';
import api from '../services/api';
import '../styles/matches.css';

export const MatchCard = ({ match, onFavoriteChange }) => {
  const { isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [isFavorite, setIsFavorite] = useState(match.is_favorite || false);
  const [loadingFav, setLoadingFav] = useState(false);
  const [copied, setCopied] = useState(false);

  const formatMatchTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
      day: 'numeric',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const handleShare = async (e) => {
    e.stopPropagation();
    const shareUrl = `${window.location.origin}/scoreboard/${match.id}`;
    const shareText = `Check out ${match.team1} vs ${match.team2} on SportSync!`;
    if (navigator.share) {
      try {
        await navigator.share({
          title: `SportSync - ${match.team1} vs ${match.team2}`,
          text: shareText,
          url: shareUrl,
        });
        return;
      } catch (err) {
        // User cancelled or unsupported
      }
    }
    if (navigator.clipboard) {
      await navigator.clipboard.writeText(shareUrl);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  const getStatusBadgeClass = (status) => {
    switch (status) {
      case 'live':
        return 'badge badge-live';
      case 'upcoming':
        return 'badge badge-upcoming';
      case 'completed':
        return 'badge badge-completed';
      default:
        return 'badge badge-upcoming';
    }
  };

  const renderCricketScore = (score, wickets, overs, status) => {
    if (status === 'upcoming') return '-';
    return `${score || 0}/${wickets || 0} (${overs || '0.0'})`;
  };

  const renderFootballScore = (score, status) => {
    if (status === 'upcoming') return '-';
    return score !== null && score !== undefined ? score : '0';
  };

  const handleToggleFavorite = async () => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }

    setLoadingFav(true);
    try {
      const res = await api.post('/favorites/toggle', {
        match_id: match.id,
      });
      const newFav = res.data?.is_favorite;
      setIsFavorite(newFav);
      if (onFavoriteChange) {
        onFavoriteChange(match.id, newFav);
      }
    } catch (err) {
      console.error('Failed to toggle favorite:', err);
    } finally {
      setLoadingFav(false);
    }
  };

  return (
    <div className="match-card">
      <div className="match-card-header">
        <div className="match-card-meta">
          <span>{match.sport}</span>
          <span>·</span>
          <span>{formatMatchTime(match.match_time)}</span>
        </div>
        <span className={getStatusBadgeClass(match.status)}>
          {match.status}
        </span>
      </div>

      <div className="match-team-row">
        <span className="match-team-name">{match.team1}</span>
        <span className="match-team-score">
          {match.sport === 'cricket'
            ? renderCricketScore(match.team1_score, match.team1_wickets, match.team1_overs, match.status)
            : renderFootballScore(match.team1_score, match.status)}
        </span>
      </div>

      <div className="match-team-row">
        <span className="match-team-name">{match.team2}</span>
        <span className="match-team-score">
          {match.sport === 'cricket'
            ? renderCricketScore(match.team2_score, match.team2_wickets, match.team2_overs, match.status)
            : renderFootballScore(match.team2_score, match.status)}
        </span>
      </div>

      <div className="match-card-footer">
        <span className="match-venue-label" title={match.venue || match.location || 'TBD'}>
          {match.venue || match.location || 'TBD'}
        </span>

        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <button
            className="match-card-icon-btn"
            onClick={handleShare}
            title={copied ? 'Link Copied!' : 'Share Match'}
          >
            {copied ? <Check size={14} color="#4ade80" /> : <Share2 size={14} />}
          </button>

          <button
            className={`favorite-toggle-btn ${!isFavorite ? 'not-favorited' : ''}`}
            onClick={handleToggleFavorite}
            disabled={loadingFav}
          >
            <Heart size={16} fill={isFavorite ? 'currentColor' : 'none'} />
            {loadingFav ? '...' : isFavorite ? 'Remove' : 'Favorite'}
          </button>
        </div>
      </div>

      <Link to={`/scoreboard/${match.id}`} className="view-scoreboard-btn">
        View Detailed Scoreboard
      </Link>
    </div>
  );
};
