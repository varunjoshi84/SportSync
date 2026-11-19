import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Heart, Trash2 } from 'lucide-react';
import api from '../services/api';
import '../styles/dashboard.css';

export const Dashboard = () => {
  const { user, updateUserPreferences } = useAuth();

  // Profile preferences state
  const [favoriteSport, setFavoriteSport] = useState(user?.favorite_sport || '');
  const [favoriteTeam, setFavoriteTeam] = useState(user?.favorite_team || '');
  const [profileMsg, setProfileMsg] = useState(null);
  const [profileLoading, setProfileLoading] = useState(false);

  // Change password state
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [passwordMsg, setPasswordMsg] = useState(null);
  const [passwordLoading, setPasswordLoading] = useState(false);

  const handleUpdateProfile = async (e) => {
    e.preventDefault();
    setProfileLoading(true);
    setProfileMsg(null);
    try {
      const res = await api.put('/users/preferences', {
        favorite_sport: favoriteSport,
        favorite_team: favoriteTeam,
      });
      updateUserPreferences({
        favorite_sport: favoriteSport,
        favorite_team: favoriteTeam,
      });
      setProfileMsg({ type: 'success', text: res.data?.message || 'Profile updated successfully!' });
    } catch (err) {
      setProfileMsg({
        type: 'error',
        text: err.response?.data?.message || 'Failed to update profile. Please try again.',
      });
    } finally {
      setProfileLoading(false);
    }
  };

  const handleChangePassword = async (e) => {
    e.preventDefault();
    if (newPassword.length <= 6) {
      setPasswordMsg({ type: 'error', text: 'New password must be more than 6 characters.' });
      return;
    }
    if (newPassword !== confirmPassword) {
      setPasswordMsg({ type: 'error', text: 'Passwords do not match!' });
      return;
    }

    setPasswordLoading(true);
    setPasswordMsg(null);
    try {
      const res = await api.post('/users/change-password', {
        current_password: currentPassword,
        new_password: newPassword,
        confirm_new_password: confirmPassword,
      });
      setPasswordMsg({ type: 'success', text: res.data?.message || 'Password changed successfully!' });
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
    } catch (err) {
      setPasswordMsg({
        type: 'error',
        text: err.response?.data?.message || 'Current password is incorrect.',
      });
    } finally {
      setPasswordLoading(false);
    }
  };

  const getInitial = () => {
    return user?.username ? user.username.charAt(0).toUpperCase() : 'U';
  };

  return (
    <div className="dashboard-container">
      <div className="dashboard-card-wrapper">
        <h1 className="dashboard-greeting">Welcome, {user?.username}!</h1>
        <p className="dashboard-subgreeting">Here's what's happening in your sports world.</p>

        <div className="dashboard-layout">
          {/* Left Sidebar Profile Card */}
          <div className="profile-sidebar-card">
            <div className="profile-avatar-row">
              <div className="profile-avatar-large">{getInitial()}</div>
              <div className="profile-user-info">
                <h4>{user?.username}</h4>
                <p>{user?.email}</p>
              </div>
            </div>

            <div className="profile-details-list">
              <p>Favorite Team: <strong>{user?.favorite_team || 'Not set'}</strong></p>
              <p>Favorite Sport: <strong>{user?.favorite_sport || 'Not set'}</strong></p>
              <p>Account Type: <strong>{user?.account_type || 'User'}</strong></p>
            </div>

            <div className="profile-actions-row">
              <Link to="/favorite-matches" className="profile-action-link">
                <Heart size={16} /> Favorites
              </Link>
              <Link to="/delete-account" className="profile-action-link danger">
                <Trash2 size={16} /> Delete Account
              </Link>
            </div>
          </div>

          {/* Right Forms Column */}
          <div className="dashboard-forms-column">
            {/* Update Profile Form */}
            <div className="settings-panel">
              <h3 className="settings-panel-title">Update Profile</h3>
              {profileMsg && (
                <div className={`alert ${profileMsg.type === 'success' ? 'alert-success' : 'alert-error'}`}>
                  {profileMsg.text}
                </div>
              )}
              <form onSubmit={handleUpdateProfile} className="auth-form">
                <div className="form-group">
                  <label className="form-label">Username</label>
                  <input
                    type="text"
                    value={user?.username || ''}
                    disabled
                    style={{ opacity: 0.7, cursor: 'not-allowed' }}
                  />
                </div>

                <div className="form-group">
                  <label className="form-label">Email</label>
                  <input
                    type="email"
                    value={user?.email || ''}
                    disabled
                    style={{ opacity: 0.7, cursor: 'not-allowed' }}
                  />
                </div>

                <div className="form-two-col">
                  <div className="form-group">
                    <label className="form-label">Favorite Sport</label>
                    <input
                      type="text"
                      placeholder="e.g. Cricket, Football"
                      value={favoriteSport}
                      onChange={(e) => setFavoriteSport(e.target.value)}
                    />
                  </div>
                  <div className="form-group">
                    <label className="form-label">Favorite Team</label>
                    <input
                      type="text"
                      placeholder="e.g. India, Real Madrid"
                      value={favoriteTeam}
                      onChange={(e) => setFavoriteTeam(e.target.value)}
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-primary"
                  style={{ width: 'fit-content', marginTop: '10px' }}
                  disabled={profileLoading}
                >
                  {profileLoading ? 'Saving...' : 'Update Profile'}
                </button>
              </form>
            </div>

            {/* Change Password Form */}
            <div className="settings-panel">
              <h3 className="settings-panel-title">Change Password</h3>
              {passwordMsg && (
                <div className={`alert ${passwordMsg.type === 'success' ? 'alert-success' : 'alert-error'}`}>
                  {passwordMsg.text}
                </div>
              )}
              <form onSubmit={handleChangePassword} className="auth-form">
                <div className="form-group">
                  <label className="form-label">Current Password</label>
                  <input
                    type="password"
                    placeholder="Enter current password"
                    value={currentPassword}
                    onChange={(e) => setCurrentPassword(e.target.value)}
                    required
                  />
                </div>

                <div className="form-two-col">
                  <div className="form-group">
                    <label className="form-label">New Password</label>
                    <input
                      type="password"
                      placeholder="More than 6 characters"
                      value={newPassword}
                      onChange={(e) => setNewPassword(e.target.value)}
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label className="form-label">Confirm New Password</label>
                    <input
                      type="password"
                      placeholder="Confirm new password"
                      value={confirmPassword}
                      onChange={(e) => setConfirmPassword(e.target.value)}
                      required
                    />
                  </div>
                </div>

                <button
                  type="submit"
                  className="btn btn-primary"
                  style={{ width: 'fit-content', marginTop: '10px' }}
                  disabled={passwordLoading}
                >
                  {passwordLoading ? 'Updating...' : 'Change Password'}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
