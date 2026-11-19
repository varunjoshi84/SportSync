import React from 'react';
import { Link } from 'react-router-dom';
import { Check } from 'lucide-react';
import '../styles/feedback.css';

export const ThankYou = () => {
  return (
    <div className="container">
      <div className="thank-you-card">
        <div className="thank-you-icon">
          <Check size={40} />
        </div>

        <h1 className="thank-you-title">Thank You!</h1>
        <p className="thank-you-desc">
          We appreciate you taking the time to share your feedback with us. Your input helps us continuously improve our platform.
        </p>

        <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '28px' }}>
          Our team will review your feedback and reach out to your inbox if necessary.
        </p>

        <div style={{ display: 'flex', justifyContent: 'center', gap: '16px' }}>
          <Link to="/" className="btn btn-primary">
            Return Home
          </Link>
          <Link to="/live-scores" className="btn btn-secondary">
            View Live Scores
          </Link>
        </div>
      </div>
    </div>
  );
};
