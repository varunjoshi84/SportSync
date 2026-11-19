import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';
import '../styles/footer.css';

export const Footer = () => {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState({ loading: false, success: null, error: null });

  const handleSubscribe = async (e) => {
    e.preventDefault();
    if (!email) return;

    setStatus({ loading: true, success: null, error: null });
    try {
      const res = await api.post('/subscriptions', { email });
      setStatus({
        loading: false,
        success: res.data?.message || 'Thank you for subscribing! We have sent a confirmation email.',
        error: null,
      });
      setEmail('');
    } catch (err) {
      setStatus({
        loading: false,
        success: null,
        error: err.response?.data?.message || 'Failed to subscribe. Please try again.',
      });
    }
  };

  return (
    <footer className="app-footer">
      <div className="container">
        <div className="footer-grid">
          {/* About Section */}
          <div className="footer-col">
            <h3>About SportSync</h3>
            <p>
              Your one-stop destination for live sports scores, updates, and news. Stay connected with your favorite sports and never miss a moment.
            </p>
          </div>

          {/* Quick Links */}
          <div className="footer-col">
            <h3>Quick Links</h3>
            <ul className="footer-links-list">
              <li><Link to="/">Home</Link></li>
              <li><Link to="/live-scores">Live Scores</Link></li>
              <li><Link to="/football">Football</Link></li>
              <li><Link to="/cricket">Cricket</Link></li>
            </ul>
          </div>

          {/* Contact Info */}
          <div className="footer-col">
            <h3>Contact Us</h3>
            <p className="footer-contact-item">Email: info@sportsync.com</p>
            <p className="footer-contact-item">Phone: +91 9631117684</p>
            <p className="footer-contact-item">
              <Link to="/feedback" style={{ color: 'var(--accent-red)', fontWeight: 600 }}>
                Feedback
              </Link>
            </p>
          </div>

          {/* Newsletter Subscription */}
          <div className="footer-col">
            <h3>Subscribe to Newsletter</h3>
            <form onSubmit={handleSubscribe} className="newsletter-form">
              <input
                type="email"
                placeholder="Enter your email"
                className="newsletter-input"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
              <button
                type="submit"
                className="newsletter-btn"
                disabled={status.loading}
              >
                {status.loading ? 'Subscribing...' : 'Subscribe'}
              </button>
            </form>

            {status.success && (
              <p style={{ color: '#4ade80', fontSize: '0.85rem', marginTop: '10px' }}>
                {status.success}
              </p>
            )}
            {status.error && (
              <p style={{ color: '#f87171', fontSize: '0.85rem', marginTop: '10px' }}>
                {status.error}
              </p>
            )}
          </div>
        </div>

        {/* Copyright */}
        <div className="footer-copyright">
          <p>&copy; {new Date().getFullYear()} SportSync. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
};
