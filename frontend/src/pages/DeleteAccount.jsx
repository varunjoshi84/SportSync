import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';
import '../styles/feedback.css';

export const DeleteAccount = () => {
  const [step, setStep] = useState(1);
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);

  const { logout } = useAuth();
  const navigate = useNavigate();

  const handleInitialClick = (e) => {
    e.preventDefault();
    setStep(2);
  };

  const handleConfirmDelete = async (e) => {
    e.preventDefault();
    if (!password) {
      setError('Password is required to confirm deletion.');
      return;
    }

    setLoading(true);
    setError(null);
    try {
      await api.post('/users/delete-account', { password });
      logout();
      navigate('/', { replace: true });
    } catch (err) {
      setError(
        err.response?.data?.message || 'Failed to delete account. Please check your password and try again.',
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container" style={{ padding: '60px 20px' }}>
      <div className="delete-account-card">
        <h2 style={{ fontSize: '1.8rem', fontWeight: 800, marginBottom: '20px', color: 'var(--text-primary)' }}>
          Delete Account
        </h2>

        {error && <div className="alert alert-error">{error}</div>}

        {step === 1 ? (
          <div>
            <p style={{ color: 'var(--text-secondary)', lineHeight: 1.6, marginBottom: '28px' }}>
              Are you sure you want to delete your account? This action cannot be undone and all your data, including favorite teams, matches, and preferences, will be permanently removed.
            </p>

            <div style={{ display: 'flex', gap: '14px' }}>
              <button
                type="button"
                className="btn btn-danger"
                onClick={handleInitialClick}
              >
                Delete My Account
              </button>
              <Link to="/dashboard" className="btn btn-secondary">
                Cancel
              </Link>
            </div>
          </div>
        ) : (
          <form onSubmit={handleConfirmDelete}>
            <div className="delete-warning-box">
              <p style={{ fontWeight: 700, marginBottom: '6px' }}>
                Warning: This action cannot be undone.
              </p>
              <p>
                All your data, including favorite teams, matches and preferences will be permanently deleted.
              </p>
            </div>

            <div className="form-group" style={{ marginBottom: '24px' }}>
              <label className="form-label">
                Enter your password to confirm deletion
              </label>
              <input
                type="password"
                placeholder="Your current password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>

            <div style={{ display: 'flex', gap: '14px' }}>
              <button
                type="submit"
                className="btn btn-danger"
                disabled={loading}
              >
                {loading ? 'Deleting...' : 'Permanently Delete My Account'}
              </button>
              <Link to="/dashboard" className="btn btn-secondary">
                Cancel
              </Link>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};
