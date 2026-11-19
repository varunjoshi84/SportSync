import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Navbar } from './components/Navbar';
import { Footer } from './components/Footer';

// Pages
import { Home } from './pages/Home';
import { Football } from './pages/Football';
import { Cricket } from './pages/Cricket';
import { LiveScores } from './pages/LiveScores';
import { Scoreboard } from './pages/Scoreboard';
import { Login } from './pages/Login';
import { Signup } from './pages/Signup';
import { Dashboard } from './pages/Dashboard';
import { DeleteAccount } from './pages/DeleteAccount';
import { FavoriteMatches } from './pages/FavoriteMatches';
import { Feedback } from './pages/Feedback';
import { ThankYou } from './pages/ThankYou';
import { Admin } from './pages/Admin';

import './styles/global.css';

export const App = () => {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Navbar />
        <main className="app-main">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/football" element={<Football />} />
            <Route path="/cricket" element={<Cricket />} />
            <Route path="/live-scores" element={<LiveScores />} />
            <Route path="/scoreboard/:id" element={<Scoreboard />} />
            <Route path="/login" element={<Login />} />
            <Route path="/signup" element={<Signup />} />
            <Route path="/feedback" element={<Feedback />} />
            <Route path="/thank-you" element={<ThankYou />} />

            {/* User Protected Routes */}
            <Route
              path="/dashboard"
              element={
                <ProtectedRoute>
                  <Dashboard />
                </ProtectedRoute>
              }
            />
            <Route
              path="/delete-account"
              element={
                <ProtectedRoute>
                  <DeleteAccount />
                </ProtectedRoute>
              }
            />
            <Route
              path="/favorite-matches"
              element={
                <ProtectedRoute>
                  <FavoriteMatches />
                </ProtectedRoute>
              }
            />

            {/* Admin Protected Routes */}
            <Route
              path="/admin"
              element={
                <ProtectedRoute requireAdmin={true}>
                  <Admin />
                </ProtectedRoute>
              }
            />

            {/* Fallback */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </main>
        <Footer />
      </AuthProvider>
    </BrowserRouter>
  );
};

export default App;
