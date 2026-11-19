import React, { useState, useEffect } from 'react';
import { Edit2, Trash2, Users, Mail, Plus, X } from 'lucide-react';
import api from '../services/api';
import '../styles/admin.css';

export const Admin = () => {
  const [activeTab, setActiveTab] = useState('matches');
  const [message, setMessage] = useState(null);
  const [error, setError] = useState(null);

  // Matches state
  const [matches, setMatches] = useState([]);
  const [loadingMatches, setLoadingMatches] = useState(true);
  const [showAddMatchModal, setShowAddMatchModal] = useState(false);
  const [showEditMatchModal, setShowEditMatchModal] = useState(false);
  const [showPlayerModal, setShowPlayerModal] = useState(false);
  const [selectedMatch, setSelectedMatch] = useState(null);

  // Match Form State
  const [matchForm, setMatchForm] = useState({
    sport: 'cricket',
    team1: '',
    team2: '',
    venue: '',
    match_time: '',
    status: 'upcoming',
    team1_score: 0,
    team2_score: 0,
    team1_wickets: 0,
    team2_wickets: 0,
    team1_overs: 0.0,
    team2_overs: 0.0,
    team1_shots: 0,
    team2_shots: 0,
    team1_possession: 50,
    team2_possession: 50,
  });

  // Player Form State
  const [matchPlayers, setMatchPlayers] = useState([]);
  const [newPlayerName, setNewPlayerName] = useState('');
  const [newPlayerTeam, setNewPlayerTeam] = useState('team1');
  const [newPlayerRole, setNewPlayerRole] = useState('Player');
  const [newPlayerScore, setNewPlayerScore] = useState(0);

  // News State
  const [newsList, setNewsList] = useState([]);
  const [loadingNews, setLoadingNews] = useState(false);
  const [showAddNewsModal, setShowAddNewsModal] = useState(false);
  const [showEditNewsModal, setShowEditNewsModal] = useState(false);
  const [selectedNews, setSelectedNews] = useState(null);
  const [newsForm, setNewsForm] = useState({ title: '', category: 'football', content: '' });

  // Feedback State
  const [feedbackList, setFeedbackList] = useState([]);
  const [loadingFeedback, setLoadingFeedback] = useState(false);

  // Fetch functions
  const fetchMatches = async () => {
    try {
      setLoadingMatches(true);
      const res = await api.get('/matches');
      setMatches(res.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoadingMatches(false);
    }
  };

  const fetchNews = async () => {
    try {
      setLoadingNews(true);
      const res = await api.get('/news/all');
      setNewsList(res.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoadingNews(false);
    }
  };

  const fetchFeedback = async () => {
    try {
      setLoadingFeedback(true);
      const res = await api.get('/feedback');
      setFeedbackList(res.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoadingFeedback(false);
    }
  };

  useEffect(() => {
    fetchMatches();
    fetchNews();
    fetchFeedback();
  }, []);

  const displayNotice = (msg, isError = false) => {
    if (isError) {
      setError(msg);
      setTimeout(() => setError(null), 4000);
    } else {
      setMessage(msg);
      setTimeout(() => setMessage(null), 4000);
    }
  };

  // --- MATCH HANDLERS ---
  const handleOpenAddMatch = () => {
    setMatchForm({
      sport: 'cricket',
      team1: '',
      team2: '',
      venue: '',
      match_time: '',
      status: 'upcoming',
      team1_score: 0,
      team2_score: 0,
      team1_wickets: 0,
      team2_wickets: 0,
      team1_overs: 0.0,
      team2_overs: 0.0,
      team1_shots: 0,
      team2_shots: 0,
      team1_possession: 50,
      team2_possession: 50,
    });
    setShowAddMatchModal(true);
  };

  const handleCreateMatch = async (e) => {
    e.preventDefault();
    try {
      await api.post('/matches', matchForm);
      setShowAddMatchModal(false);
      displayNotice('Match added successfully!');
      fetchMatches();
    } catch (err) {
      displayNotice(err.response?.data?.message || 'Failed to add match', true);
    }
  };

  const handleOpenEditMatch = (match) => {
    setSelectedMatch(match);
    // Format datetime for datetime-local input
    let formattedTime = '';
    if (match.match_time) {
      const d = new Date(match.match_time);
      formattedTime = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    }

    setMatchForm({
      sport: match.sport,
      team1: match.team1,
      team2: match.team2,
      venue: match.venue || '',
      match_time: formattedTime,
      status: match.status,
      team1_score: match.team1_score || 0,
      team2_score: match.team2_score || 0,
      team1_wickets: match.team1_wickets || 0,
      team2_wickets: match.team2_wickets || 0,
      team1_overs: match.team1_overs || 0.0,
      team2_overs: match.team2_overs || 0.0,
      team1_shots: match.team1_shots || 0,
      team2_shots: match.team2_shots || 0,
      team1_possession: match.team1_possession || 50,
      team2_possession: match.team2_possession || 50,
    });
    setShowEditMatchModal(true);
  };

  const handleUpdateMatch = async (e) => {
    e.preventDefault();
    try {
      await api.put(`/matches/${selectedMatch.id}`, matchForm);
      setShowEditMatchModal(false);
      displayNotice('Match updated successfully!');
      fetchMatches();
    } catch (err) {
      displayNotice(err.response?.data?.message || 'Failed to update match', true);
    }
  };

  const handleDeleteMatch = async (id) => {
    if (!window.confirm('Are you sure you want to delete this match?')) return;
    try {
      await api.delete(`/matches/${id}`);
      displayNotice('Match deleted successfully!');
      fetchMatches();
    } catch (err) {
      displayNotice('Failed to delete match', true);
    }
  };

  // --- PLAYERS MODAL HANDLERS ---
  const handleOpenPlayers = async (match) => {
    setSelectedMatch(match);
    setNewPlayerTeam(match.team1);
    setNewPlayerName('');
    setNewPlayerScore(0);
    try {
      const res = await api.get(`/players?match_id=${match.id}`);
      setMatchPlayers(res.data?.players || []);
      setShowPlayerModal(true);
    } catch (err) {
      displayNotice('Failed to load players', true);
    }
  };

  const handleAddPlayer = async (e) => {
    e.preventDefault();
    if (!newPlayerName) return;

    try {
      await api.post('/players', {
        match_id: selectedMatch.id,
        team: newPlayerTeam,
        name: newPlayerName,
        role: newPlayerRole,
        score: parseInt(newPlayerScore, 10) || 0,
        sport: selectedMatch.sport,
      });
      setNewPlayerName('');
      setNewPlayerScore(0);
      displayNotice('Player added successfully!');
      // Refresh players
      const res = await api.get(`/players?match_id=${selectedMatch.id}`);
      setMatchPlayers(res.data?.players || []);
      fetchMatches();
    } catch (err) {
      displayNotice('Failed to add player', true);
    }
  };

  const handleDeletePlayer = async (playerId) => {
    try {
      await api.delete(`/players/${playerId}`);
      displayNotice('Player removed');
      const res = await api.get(`/players?match_id=${selectedMatch.id}`);
      setMatchPlayers(res.data?.players || []);
      fetchMatches();
    } catch (err) {
      displayNotice('Failed to delete player', true);
    }
  };

  // --- NEWS HANDLERS ---
  const handleCreateNews = async (e) => {
    e.preventDefault();
    try {
      await api.post('/news', newsForm);
      setShowAddNewsModal(false);
      setNewsForm({ title: '', category: 'football', content: '' });
      displayNotice('News article added successfully!');
      fetchNews();
    } catch (err) {
      displayNotice('Failed to add news article', true);
    }
  };

  const handleOpenEditNews = (news) => {
    setSelectedNews(news);
    setNewsForm({
      title: news.title,
      category: news.category,
      content: news.content,
    });
    setShowEditNewsModal(true);
  };

  const handleUpdateNews = async (e) => {
    e.preventDefault();
    try {
      await api.put(`/news/${selectedNews.id}`, newsForm);
      setShowEditNewsModal(false);
      displayNotice('News article updated successfully!');
      fetchNews();
    } catch (err) {
      displayNotice('Failed to update news article', true);
    }
  };

  const handleDeleteNews = async (id) => {
    if (!window.confirm('Are you sure you want to delete this article?')) return;
    try {
      await api.delete(`/news/${id}`);
      displayNotice('News article deleted!');
      fetchNews();
    } catch (err) {
      displayNotice('Failed to delete news', true);
    }
  };

  // --- FEEDBACK HANDLERS ---
  const handleUpdateFeedbackStatus = async (id, newStatus) => {
    try {
      await api.put(`/feedback/${id}/status`, { status: newStatus });
      displayNotice('Feedback status updated!');
      fetchFeedback();
    } catch (err) {
      displayNotice('Failed to update status', true);
    }
  };

  const handleDeleteFeedback = async (id) => {
    if (!window.confirm('Are you sure you want to delete this feedback?')) return;
    try {
      await api.delete(`/feedback/${id}`);
      displayNotice('Feedback deleted!');
      fetchFeedback();
    } catch (err) {
      displayNotice('Failed to delete feedback', true);
    }
  };

  return (
    <div className="admin-page-container">
      <div className="admin-header-row">
        <h1 className="admin-header-title">Admin Dashboard</h1>
      </div>

      {message && <div className="alert alert-success">{message}</div>}
      {error && <div className="alert alert-error">{error}</div>}

      {/* Tabs */}
      <div className="admin-tabs-bar">
        <button
          className={`admin-tab-btn ${activeTab === 'matches' ? 'active' : ''}`}
          onClick={() => setActiveTab('matches')}
        >
          Matches
        </button>
        <button
          className={`admin-tab-btn ${activeTab === 'news' ? 'active' : ''}`}
          onClick={() => setActiveTab('news')}
        >
          News
        </button>
        <button
          className={`admin-tab-btn ${activeTab === 'feedback' ? 'active' : ''}`}
          onClick={() => setActiveTab('feedback')}
        >
          Feedback
        </button>
      </div>

      {/* MATCHES TAB */}
      {activeTab === 'matches' && (
        <div className="admin-content-box">
          <div className="admin-box-header">
            <h2 className="admin-box-title">Match Management</h2>
            <button className="btn btn-primary" onClick={handleOpenAddMatch}>
              <Plus size={16} /> Add New Match
            </button>
          </div>

          <div className="table-responsive">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Sport</th>
                  <th>Teams</th>
                  <th>Venue</th>
                  <th>Match Time</th>
                  <th>Status</th>
                  <th>Scores</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {loadingMatches ? (
                  <tr>
                    <td colSpan="8" style={{ textAlign: 'center', padding: '30px 0', color: 'var(--text-secondary)' }}>
                      Loading matches...
                    </td>
                  </tr>
                ) : matches.length > 0 ? (
                  matches.map((m) => (
                    <tr key={m.id}>
                      <td>#{m.id}</td>
                      <td>
                        <span className="category-tag">{m.sport}</span>
                      </td>
                      <td><strong>{m.team1}</strong> vs <strong>{m.team2}</strong></td>
                      <td>{m.venue || 'TBD'}</td>
                      <td>{new Date(m.match_time).toLocaleDateString()}</td>
                      <td>
                        <span className={`badge ${m.status === 'live' ? 'badge-live' : m.status === 'completed' ? 'badge-completed' : 'badge-upcoming'}`}>
                          {m.status}
                        </span>
                      </td>
                      <td>{m.team1_score} - {m.team2_score}</td>
                      <td>
                        <div className="action-btns-group">
                          <button
                            className="action-icon-btn"
                            title="Edit Match Details"
                            onClick={() => handleOpenEditMatch(m)}
                          >
                            <Edit2 size={16} />
                          </button>
                          <button
                            className="action-icon-btn"
                            title="Manage Team Players"
                            onClick={() => handleOpenPlayers(m)}
                          >
                            <Users size={16} />
                          </button>
                          <button
                            className="action-icon-btn danger"
                            title="Delete Match"
                            onClick={() => handleDeleteMatch(m.id)}
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="8" style={{ textAlign: 'center', padding: '30px 0', color: 'var(--text-muted)' }}>
                      No matches found.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* NEWS TAB */}
      {activeTab === 'news' && (
        <div className="admin-content-box">
          <div className="admin-box-header">
            <h2 className="admin-box-title">News Management</h2>
            <button
              className="btn btn-primary"
              onClick={() => {
                setNewsForm({ title: '', category: 'football', content: '' });
                setShowAddNewsModal(true);
              }}
            >
              <Plus size={16} /> Add News Article
            </button>
          </div>

          <div className="table-responsive">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {loadingNews ? (
                  <tr>
                    <td colSpan="4" style={{ textAlign: 'center', padding: '30px 0', color: 'var(--text-secondary)' }}>
                      Loading news...
                    </td>
                  </tr>
                ) : newsList.length > 0 ? (
                  newsList.map((item) => (
                    <tr key={item.id}>
                      <td><strong>{item.title}</strong></td>
                      <td><span className="category-tag">{item.category}</span></td>
                      <td>{new Date(item.created_at).toLocaleDateString()}</td>
                      <td>
                        <div className="action-btns-group">
                          <button
                            className="action-icon-btn"
                            onClick={() => handleOpenEditNews(item)}
                          >
                            <Edit2 size={16} />
                          </button>
                          <button
                            className="action-icon-btn danger"
                            onClick={() => handleDeleteNews(item.id)}
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="4" style={{ textAlign: 'center', padding: '30px 0', color: 'var(--text-muted)' }}>
                      No news articles found.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* FEEDBACK TAB */}
      {activeTab === 'feedback' && (
        <div className="admin-content-box">
          <div className="admin-box-header">
            <h2 className="admin-box-title">User Feedback</h2>
          </div>

          <div className="table-responsive">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Subject</th>
                  <th>Message</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {loadingFeedback ? (
                  <tr>
                    <td colSpan="7" style={{ textAlign: 'center', padding: '30px 0', color: 'var(--text-secondary)' }}>
                      Loading feedback...
                    </td>
                  </tr>
                ) : feedbackList.length > 0 ? (
                  feedbackList.map((fb) => (
                    <tr key={fb.id}>
                      <td><strong>{fb.name}</strong></td>
                      <td>{fb.email}</td>
                      <td>{fb.subject}</td>
                      <td style={{ maxWidth: '240px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }} title={fb.message}>
                        {fb.message}
                      </td>
                      <td>
                        <select
                          value={fb.status}
                          onChange={(e) => handleUpdateFeedbackStatus(fb.id, e.target.value)}
                          style={{ padding: '4px 8px', fontSize: '0.85rem' }}
                        >
                          <option value="pending">Pending</option>
                          <option value="read">Read</option>
                          <option value="responded">Responded</option>
                        </select>
                      </td>
                      <td>{new Date(fb.created_at).toLocaleDateString()}</td>
                      <td>
                        <div className="action-btns-group">
                          <a
                            href={`mailto:${fb.email}?subject=Re: ${encodeURIComponent(fb.subject)}`}
                            className="action-icon-btn"
                            title="Reply by email"
                          >
                            <Mail size={16} />
                          </a>
                          <button
                            className="action-icon-btn danger"
                            title="Delete Feedback"
                            onClick={() => handleDeleteFeedback(fb.id)}
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="7" style={{ textAlign: 'center', padding: '30px 0', color: 'var(--text-muted)' }}>
                      No feedback submitted yet.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* ADD / EDIT MATCH MODAL */}
      {(showAddMatchModal || showEditMatchModal) && (
        <div className="modal-overlay">
          <div className="modal-dialog">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
              <h3 className="modal-title" style={{ margin: 0 }}>
                {showAddMatchModal ? 'Add New Match' : 'Edit Match Details'}
              </h3>
              <button
                style={{ background: 'transparent', color: 'var(--text-muted)' }}
                onClick={() => {
                  setShowAddMatchModal(false);
                  setShowEditMatchModal(false);
                }}
              >
                <X size={20} />
              </button>
            </div>

            <form onSubmit={showAddMatchModal ? handleCreateMatch : handleUpdateMatch} className="auth-form">
              <div className="form-two-col">
                <div className="form-group">
                  <label className="form-label">Sport</label>
                  <select
                    value={matchForm.sport}
                    onChange={(e) => setMatchForm({ ...matchForm, sport: e.target.value })}
                    required
                  >
                    <option value="cricket">Cricket</option>
                    <option value="football">Football</option>
                  </select>
                </div>
                <div className="form-group">
                  <label className="form-label">Status</label>
                  <select
                    value={matchForm.status}
                    onChange={(e) => setMatchForm({ ...matchForm, status: e.target.value })}
                  >
                    <option value="upcoming">Upcoming</option>
                    <option value="live">Live</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
              </div>

              <div className="form-two-col">
                <div className="form-group">
                  <label className="form-label">Team 1</label>
                  <input
                    type="text"
                    placeholder="e.g. India"
                    value={matchForm.team1}
                    onChange={(e) => setMatchForm({ ...matchForm, team1: e.target.value })}
                    required
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Team 2</label>
                  <input
                    type="text"
                    placeholder="e.g. Australia"
                    value={matchForm.team2}
                    onChange={(e) => setMatchForm({ ...matchForm, team2: e.target.value })}
                    required
                  />
                </div>
              </div>

              <div className="form-two-col">
                <div className="form-group">
                  <label className="form-label">Venue</label>
                  <input
                    type="text"
                    placeholder="e.g. Melbourne Cricket Ground"
                    value={matchForm.venue}
                    onChange={(e) => setMatchForm({ ...matchForm, venue: e.target.value })}
                    required
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Match Date & Time</label>
                  <input
                    type="datetime-local"
                    value={matchForm.match_time}
                    onChange={(e) => setMatchForm({ ...matchForm, match_time: e.target.value })}
                    required
                  />
                </div>
              </div>

              {/* Dynamic Scores and Stats */}
              <div className="form-two-col">
                <div className="form-group">
                  <label className="form-label">Team 1 Score / Runs</label>
                  <input
                    type="number"
                    min="0"
                    value={matchForm.team1_score}
                    onChange={(e) => setMatchForm({ ...matchForm, team1_score: parseInt(e.target.value, 10) || 0 })}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Team 2 Score / Runs</label>
                  <input
                    type="number"
                    min="0"
                    value={matchForm.team2_score}
                    onChange={(e) => setMatchForm({ ...matchForm, team2_score: parseInt(e.target.value, 10) || 0 })}
                  />
                </div>
              </div>

              {matchForm.sport === 'cricket' && (
                <>
                  <div className="form-two-col">
                    <div className="form-group">
                      <label className="form-label">Team 1 Wickets (0-10)</label>
                      <input
                        type="number"
                        min="0"
                        max="10"
                        value={matchForm.team1_wickets}
                        onChange={(e) => setMatchForm({ ...matchForm, team1_wickets: parseInt(e.target.value, 10) || 0 })}
                      />
                    </div>
                    <div className="form-group">
                      <label className="form-label">Team 2 Wickets (0-10)</label>
                      <input
                        type="number"
                        min="0"
                        max="10"
                        value={matchForm.team2_wickets}
                        onChange={(e) => setMatchForm({ ...matchForm, team2_wickets: parseInt(e.target.value, 10) || 0 })}
                      />
                    </div>
                  </div>
                  <div className="form-two-col">
                    <div className="form-group">
                      <label className="form-label">Team 1 Overs</label>
                      <input
                        type="number"
                        step="0.1"
                        min="0"
                        value={matchForm.team1_overs}
                        onChange={(e) => setMatchForm({ ...matchForm, team1_overs: parseFloat(e.target.value) || 0.0 })}
                      />
                    </div>
                    <div className="form-group">
                      <label className="form-label">Team 2 Overs</label>
                      <input
                        type="number"
                        step="0.1"
                        min="0"
                        value={matchForm.team2_overs}
                        onChange={(e) => setMatchForm({ ...matchForm, team2_overs: parseFloat(e.target.value) || 0.0 })}
                      />
                    </div>
                  </div>
                </>
              )}

              {matchForm.sport === 'football' && (
                <>
                  <div className="form-two-col">
                    <div className="form-group">
                      <label className="form-label">Team 1 Shots on Target</label>
                      <input
                        type="number"
                        min="0"
                        value={matchForm.team1_shots}
                        onChange={(e) => setMatchForm({ ...matchForm, team1_shots: parseInt(e.target.value, 10) || 0 })}
                      />
                    </div>
                    <div className="form-group">
                      <label className="form-label">Team 2 Shots on Target</label>
                      <input
                        type="number"
                        min="0"
                        value={matchForm.team2_shots}
                        onChange={(e) => setMatchForm({ ...matchForm, team2_shots: parseInt(e.target.value, 10) || 0 })}
                      />
                    </div>
                  </div>
                  <div className="form-two-col">
                    <div className="form-group">
                      <label className="form-label">Team 1 Possession (%)</label>
                      <input
                        type="number"
                        min="0"
                        max="100"
                        value={matchForm.team1_possession}
                        onChange={(e) => setMatchForm({ ...matchForm, team1_possession: parseInt(e.target.value, 10) || 50 })}
                      />
                    </div>
                    <div className="form-group">
                      <label className="form-label">Team 2 Possession (%)</label>
                      <input
                        type="number"
                        min="0"
                        max="100"
                        value={matchForm.team2_possession}
                        onChange={(e) => setMatchForm({ ...matchForm, team2_possession: parseInt(e.target.value, 10) || 50 })}
                      />
                    </div>
                  </div>
                </>
              )}

              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => {
                    setShowAddMatchModal(false);
                    setShowEditMatchModal(false);
                  }}
                >
                  Cancel
                </button>
                <button type="submit" className="btn btn-primary">
                  {showAddMatchModal ? 'Create Match' : 'Save Changes'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MANAGE PLAYERS MODAL */}
      {showPlayerModal && selectedMatch && (
        <div className="modal-overlay">
          <div className="modal-dialog">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
              <h3 className="modal-title" style={{ margin: 0 }}>
                Manage Players: {selectedMatch.team1} vs {selectedMatch.team2}
              </h3>
              <button
                style={{ background: 'transparent', color: 'var(--text-muted)' }}
                onClick={() => setShowPlayerModal(false)}
              >
                <X size={20} />
              </button>
            </div>

            {/* Current Players List */}
            <div className="roster-card">
              <h4 style={{ marginBottom: '12px', fontSize: '1rem', fontWeight: 600 }}>Current Roster</h4>
              {matchPlayers.length > 0 ? (
                <div style={{ maxHeight: '220px', overflowY: 'auto' }}>
                  {matchPlayers.map((p) => (
                    <div key={p.id} className="roster-item-row">
                      <span>
                        <strong>{p.name}</strong> ({p.team}) · <em>{p.role}</em> · Score: {p.score}
                      </span>
                      <button
                        className="action-icon-btn danger"
                        onClick={() => handleDeletePlayer(p.id)}
                        title="Delete player"
                      >
                        <Trash2 size={14} />
                      </button>
                    </div>
                  ))}
                </div>
              ) : (
                <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>No players added yet.</p>
              )}
            </div>

            {/* Add New Player Form */}
            <form onSubmit={handleAddPlayer} className="settings-panel">
              <h4 style={{ marginBottom: '14px', fontSize: '1rem', fontWeight: 600 }}>Add Player</h4>
              <div className="form-two-col">
                <div className="form-group">
                  <label className="form-label">Player Name</label>
                  <input
                    type="text"
                    placeholder="e.g. Virat Kohli"
                    value={newPlayerName}
                    onChange={(e) => setNewPlayerName(e.target.value)}
                    required
                  />
                </div>
                <div className="form-group">
                  <label className="form-label">Team</label>
                  <select
                    value={newPlayerTeam}
                    onChange={(e) => setNewPlayerTeam(e.target.value)}
                  >
                    <option value={selectedMatch.team1}>{selectedMatch.team1}</option>
                    <option value={selectedMatch.team2}>{selectedMatch.team2}</option>
                  </select>
                </div>
              </div>

              <div className="form-two-col">
                <div className="form-group">
                  <label className="form-label">Role</label>
                  <select
                    value={newPlayerRole}
                    onChange={(e) => setNewPlayerRole(e.target.value)}
                  >
                    {selectedMatch.sport === 'cricket' ? (
                      <>
                        <option value="Batsman">Batsman</option>
                        <option value="Bowler">Bowler</option>
                        <option value="All-rounder">All-rounder</option>
                        <option value="Wicket-keeper">Wicket-keeper</option>
                        <option value="Captain">Captain</option>
                        <option value="Player">Player</option>
                      </>
                    ) : (
                      <>
                        <option value="Forward">Forward</option>
                        <option value="Midfielder">Midfielder</option>
                        <option value="Defender">Defender</option>
                        <option value="Goalkeeper">Goalkeeper</option>
                        <option value="Captain">Captain</option>
                        <option value="Substitute">Substitute</option>
                        <option value="Player">Player</option>
                      </>
                    )}
                  </select>
                </div>
                <div className="form-group">
                  <label className="form-label">Score / Goals / Runs</label>
                  <input
                    type="number"
                    min="0"
                    value={newPlayerScore}
                    onChange={(e) => setNewPlayerScore(e.target.value)}
                  />
                </div>
              </div>

              <button type="submit" className="btn btn-primary" style={{ marginTop: '10px' }}>
                <Plus size={16} /> Add Player to Roster
              </button>
            </form>

            <div className="modal-footer">
              <button
                type="button"
                className="btn btn-secondary"
                onClick={() => setShowPlayerModal(false)}
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ADD / EDIT NEWS MODAL */}
      {(showAddNewsModal || showEditNewsModal) && (
        <div className="modal-overlay">
          <div className="modal-dialog">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
              <h3 className="modal-title" style={{ margin: 0 }}>
                {showAddNewsModal ? 'Add News Article' : 'Edit News Article'}
              </h3>
              <button
                style={{ background: 'transparent', color: 'var(--text-muted)' }}
                onClick={() => {
                  setShowAddNewsModal(false);
                  setShowEditNewsModal(false);
                }}
              >
                <X size={20} />
              </button>
            </div>

            <form onSubmit={showAddNewsModal ? handleCreateNews : handleUpdateNews} className="auth-form">
              <div className="form-group">
                <label className="form-label">Title</label>
                <input
                  type="text"
                  placeholder="Article title"
                  value={newsForm.title}
                  onChange={(e) => setNewsForm({ ...newsForm, title: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label className="form-label">Category</label>
                <select
                  value={newsForm.category}
                  onChange={(e) => setNewsForm({ ...newsForm, category: e.target.value })}
                  required
                >
                  <option value="football">Football</option>
                  <option value="cricket">Cricket</option>
                </select>
              </div>

              <div className="form-group">
                <label className="form-label">Content</label>
                <textarea
                  rows="5"
                  placeholder="Article content..."
                  value={newsForm.content}
                  onChange={(e) => setNewsForm({ ...newsForm, content: e.target.value })}
                  required
                />
              </div>

              <div className="modal-footer">
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => {
                    setShowAddNewsModal(false);
                    setShowEditNewsModal(false);
                  }}
                >
                  Cancel
                </button>
                <button type="submit" className="btn btn-primary">
                  {showAddNewsModal ? 'Add Article' : 'Save Article'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
