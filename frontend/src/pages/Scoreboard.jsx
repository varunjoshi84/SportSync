import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { ArrowLeft, MapPin, Clock } from 'lucide-react';
import api from '../services/api';
import '../styles/scoreboard.css';

export const Scoreboard = () => {
  const { id } = useParams();
  const { isAdmin } = useAuth();
  const [match, setMatch] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Admin in-place score updater state
  const [selectedPlayerId, setSelectedPlayerId] = useState('');
  const [updateScoreValue, setUpdateScoreValue] = useState('');
  const [scoreUpdateMsg, setScoreUpdateMsg] = useState(null);

  const fetchMatchDetails = async () => {
    try {
      setLoading(true);
      const res = await api.get(`/matches/${id}`);
      setMatch(res.data);
    } catch (err) {
      setError('Match not found or error loading scoreboard');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMatchDetails();
  }, [id]);

  const handleUpdatePlayerScore = async (e) => {
    e.preventDefault();
    if (!selectedPlayerId || updateScoreValue === '') return;

    try {
      await api.put(`/players/${selectedPlayerId}/score`, {
        score: parseInt(updateScoreValue, 10),
      });
      setScoreUpdateMsg({ type: 'success', text: 'Player score updated successfully!' });
      setUpdateScoreValue('');
      // Reload match details
      fetchMatchDetails();
    } catch (err) {
      setScoreUpdateMsg({ type: 'error', text: 'Failed to update player score.' });
    }
  };

  if (loading) {
    return (
      <div className="scoreboard-container" style={{ textAlign: 'center', color: 'var(--text-secondary)' }}>
        Loading detailed scoreboard...
      </div>
    );
  }

  if (error || !match) {
    return (
      <div className="scoreboard-container">
        <div className="alert alert-error">{error || 'Match not found'}</div>
        <Link to="/" className="scoreboard-back-btn">
          <ArrowLeft size={16} /> Back to Home
        </Link>
      </div>
    );
  }

  const team1Players = match.team1_players || [];
  const team2Players = match.team2_players || [];

  // Cricket batsmen & bowlers
  const team1Batsmen = team1Players.filter(
    (p) => p.role === 'Batsman' || p.role === 'All-rounder' || p.role === 'Captain' || p.role === 'Player',
  );
  const team2Bowlers = team2Players.filter(
    (p) => p.role === 'Bowler' || p.role === 'All-rounder' || p.role === 'Player',
  );

  // Football lineups & goal events
  const team1Starters = team1Players.filter((p) => !p.role.toLowerCase().includes('substitute'));
  const team1Substitutes = team1Players.filter((p) => p.role.toLowerCase().includes('substitute'));
  const team2Starters = team2Players.filter((p) => !p.role.toLowerCase().includes('substitute'));
  const team2Substitutes = team2Players.filter((p) => p.role.toLowerCase().includes('substitute'));

  // Football goal events
  const goalScorers = [...team1Players, ...team2Players]
    .filter((p) => p.score > 0)
    .map((p, idx) => ({
      name: p.name,
      team: p.team,
      time: 15 + idx * 25,
    }));

  // Top performer for Cricket
  const allPlayers = [...team1Players, ...team2Players];
  const topPerformer = allPlayers.length > 0
    ? allPlayers.reduce((max, p) => (p.score > max.score ? p : max), allPlayers[0])
    : null;

  return (
    <div className="scoreboard-container">
      <Link to={`/${match.sport}`} className="scoreboard-back-btn">
        <ArrowLeft size={18} /> Back to {match.sport.charAt(0).toUpperCase() + match.sport.slice(1)} Matches
      </Link>

      {scoreUpdateMsg && (
        <div className={`alert ${scoreUpdateMsg.type === 'success' ? 'alert-success' : 'alert-error'}`}>
          {scoreUpdateMsg.text}
        </div>
      )}

      {/* Main Score Card Banner */}
      <div className="scoreboard-main-card">
        <div className="scoreboard-header">
          <h1 className="scoreboard-match-title">
            {match.team1} vs {match.team2}
          </h1>
          <span className={`badge ${match.status === 'live' ? 'badge-live' : match.status === 'completed' ? 'badge-completed' : 'badge-upcoming'}`}>
            {match.status}
          </span>
        </div>

        <div className="scoreboard-meta-row">
          <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
            <MapPin size={16} color="var(--accent-red)" />
            <span>{match.venue || match.location || 'TBD'}</span>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
            <Clock size={16} color="var(--accent-red)" />
            <span>{new Date(match.match_time).toLocaleString()}</span>
          </div>
        </div>

        {/* Score Duel Box */}
        <div className="scoreboard-score-duel">
          <div className="scoreboard-team-box">
            <h3>{match.team1}</h3>
            <div className="big-score">
              {match.status === 'upcoming' ? '-' : match.team1_score}
            </div>
            {match.sport === 'cricket' && match.status !== 'upcoming' && (
              <div className="cricket-overs">
                {match.team1_wickets} wkts ({match.team1_overs} ov)
              </div>
            )}
          </div>
          <div className="scoreboard-team-box">
            <h3>{match.team2}</h3>
            <div className="big-score">
              {match.status === 'upcoming' ? '-' : match.team2_score}
            </div>
            {match.sport === 'cricket' && match.status !== 'upcoming' && (
              <div className="cricket-overs">
                {match.team2_wickets} wkts ({match.team2_overs} ov)
              </div>
            )}
          </div>
        </div>

        {/* Completed Winner Banner */}
        {match.status === 'completed' && (
          <div className={`winner-banner ${match.winner === 'Draw' ? 'draw' : 'won'}`}>
            {match.winner === 'Draw' ? 'Match ended in a Draw' : `${match.winner} Won`}
          </div>
        )}

        {/* Admin Player Score Updater */}
        {isAdmin && (match.status === 'live' || match.status === 'completed') && (
          <div className="admin-player-score-box">
            <h4 style={{ color: '#ffffff', marginBottom: '14px', fontWeight: 700 }}>
              Update Player Scores (Admin)
            </h4>
            <form onSubmit={handleUpdatePlayerScore} className="admin-score-form">
              <div>
                <label style={{ display: 'block', fontSize: '0.85rem', color: 'var(--text-secondary)', marginBottom: '4px' }}>
                  Select Player
                </label>
                <select
                  value={selectedPlayerId}
                  onChange={(e) => setSelectedPlayerId(e.target.value)}
                  style={{ width: '100%' }}
                  required
                >
                  <option value="">-- Select Player --</option>
                  {team1Players.length > 0 && (
                    <optgroup label={match.team1}>
                      {team1Players.map((p) => (
                        <option key={p.id} value={p.id}>
                          {p.name} - {p.role} (Current: {p.score})
                        </option>
                      ))}
                    </optgroup>
                  )}
                  {team2Players.length > 0 && (
                    <optgroup label={match.team2}>
                      {team2Players.map((p) => (
                        <option key={p.id} value={p.id}>
                          {p.name} - {p.role} (Current: {p.score})
                        </option>
                      ))}
                    </optgroup>
                  )}
                </select>
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '0.85rem', color: 'var(--text-secondary)', marginBottom: '4px' }}>
                  New Score
                </label>
                <input
                  type="number"
                  min="0"
                  value={updateScoreValue}
                  onChange={(e) => setUpdateScoreValue(e.target.value)}
                  style={{ width: '100%' }}
                  required
                />
              </div>

              <button type="submit" className="btn btn-primary" style={{ width: 'fit-content' }}>
                Update Score
              </button>
            </form>
          </div>
        )}
      </div>

      {/* Cricket Section */}
      {match.sport === 'cricket' && (
        <div className="scorecard-section">
          <h3>Cricket Scorecard</h3>

          {/* Team 1 Batting */}
          <h4>{match.team1} Batting</h4>
          <div className="table-responsive">
            <table className="scorecard-table">
              <thead>
                <tr>
                  <th>Batsman</th>
                  <th>R</th>
                  <th>B</th>
                  <th>4s</th>
                  <th>6s</th>
                  <th>SR</th>
                  <th>Dismissal</th>
                </tr>
              </thead>
              <tbody>
                {team1Batsmen.length > 0 ? (
                  team1Batsmen.map((player) => (
                    <tr key={player.id}>
                      <td><strong>{player.name}</strong></td>
                      <td>{player.score}</td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="7" style={{ textAlign: 'center', color: 'var(--text-muted)' }}>
                      No player data available
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {/* Team 2 Bowling */}
          <h4>{match.team2} Bowling</h4>
          <div className="table-responsive">
            <table className="scorecard-table">
              <thead>
                <tr>
                  <th>Bowler</th>
                  <th>O</th>
                  <th>M</th>
                  <th>R</th>
                  <th>W</th>
                  <th>Econ</th>
                </tr>
              </thead>
              <tbody>
                {team2Bowlers.length > 0 ? (
                  team2Bowlers.map((player) => (
                    <tr key={player.id}>
                      <td><strong>{player.name}</strong></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="6" style={{ textAlign: 'center', color: 'var(--text-muted)' }}>
                      No player data available
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {/* Match Summary */}
          <h4>Match Summary</h4>
          <div className="match-summary-box">
            <p>
              <strong>Result: </strong>
              {match.status === 'completed'
                ? match.team1_score > match.team2_score
                  ? `${match.team1} won`
                  : `${match.team2} won`
                : 'Match in progress'}
            </p>
            {topPerformer && topPerformer.score > 0 && (
              <p>
                <strong>Top Performer: </strong>
                {topPerformer.name} ({topPerformer.team}) - {topPerformer.score} runs
              </p>
            )}
            <p>
              <strong>Match Highlights: </strong>
              {match.status === 'upcoming'
                ? 'Match yet to start.'
                : 'Match highlights will be updated as the game progresses.'}
            </p>
          </div>
        </div>
      )}

      {/* Football Section */}
      {match.sport === 'football' && (
        <div className="scorecard-section">
          <h3>Football Match Details</h3>

          {/* Match Stats */}
          <div className="football-stats-grid">
            <div className="football-stat-card">
              <h4>Possession</h4>
              <div className="football-stat-values">
                <span>{match.team1_possession || 50}%</span>
                <span style={{ color: 'var(--text-muted)' }}>-</span>
                <span>{match.team2_possession || 50}%</span>
              </div>
            </div>
            <div className="football-stat-card">
              <h4>Shots on Target</h4>
              <div className="football-stat-values">
                <span>{match.team1_shots || 0}</span>
                <span style={{ color: 'var(--text-muted)' }}>-</span>
                <span>{match.team2_shots || 0}</span>
              </div>
            </div>
            <div className="football-stat-card">
              <h4>Corners</h4>
              <div className="football-stat-values">
                <span>-</span>
                <span style={{ color: 'var(--text-muted)' }}>-</span>
                <span>-</span>
              </div>
            </div>
          </div>

          {/* Team Lineups */}
          <div className="lineups-grid">
            <div className="lineup-box">
              <h4 style={{ marginBottom: '16px' }}>{match.team1} Lineup</h4>
              <h5>Starting XI</h5>
              <ul className="lineup-list">
                {team1Starters.length > 0 ? (
                  team1Starters.map((p, idx) => (
                    <li key={p.id}>
                      {idx + 1}. {p.name} {p.role === 'Goalkeeper' ? '(GK)' : ''} {p.role.includes('Captain') ? '(C)' : ''}
                    </li>
                  ))
                ) : (
                  <li>No player data available</li>
                )}
              </ul>

              {team1Substitutes.length > 0 && (
                <>
                  <h5 style={{ marginTop: '20px' }}>Substitutes</h5>
                  <ul className="lineup-list">
                    {team1Substitutes.map((p, idx) => (
                      <li key={p.id}>
                        {idx + 1}. {p.name}
                      </li>
                    ))}
                  </ul>
                </>
              )}
            </div>

            <div className="lineup-box">
              <h4 style={{ marginBottom: '16px' }}>{match.team2} Lineup</h4>
              <h5>Starting XI</h5>
              <ul className="lineup-list">
                {team2Starters.length > 0 ? (
                  team2Starters.map((p, idx) => (
                    <li key={p.id}>
                      {idx + 1}. {p.name} {p.role === 'Goalkeeper' ? '(GK)' : ''} {p.role.includes('Captain') ? '(C)' : ''}
                    </li>
                  ))
                ) : (
                  <li>No player data available</li>
                )}
              </ul>

              {team2Substitutes.length > 0 && (
                <>
                  <h5 style={{ marginTop: '20px' }}>Substitutes</h5>
                  <ul className="lineup-list">
                    {team2Substitutes.map((p, idx) => (
                      <li key={p.id}>
                        {idx + 1}. {p.name}
                      </li>
                    ))}
                  </ul>
                </>
              )}
            </div>
          </div>

          {/* Match Events */}
          <h4>Match Events</h4>
          <div className="match-summary-box">
            {match.status === 'upcoming' ? (
              <p style={{ textAlign: 'center', color: 'var(--text-muted)' }}>
                Match has not started yet. Events will be updated during the match.
              </p>
            ) : goalScorers.length > 0 ? (
              <div className="events-timeline">
                {goalScorers.map((scorer, idx) => (
                  <div key={idx} className="event-timeline-item">
                    <span className="event-minute">{scorer.time}'</span>
                    <span className="event-text">
                      <span className="goal-tag">&#x26BD; GOAL</span> - {scorer.name} ({scorer.team})
                    </span>
                  </div>
                ))}
              </div>
            ) : (
              <p style={{ textAlign: 'center', color: 'var(--text-muted)' }}>
                No goals scored yet.
              </p>
            )}
          </div>
        </div>
      )}
    </div>
  );
};
