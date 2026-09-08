import React, { useContext } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

import { AuthProvider, AuthContext } from './contexts/AuthContext';
import Navbar from './components/Navbar';
import Login from './components/Login';
import Register from './components/Register';
import WebsiteList from './components/WebsiteList';
import AddWebsite from './components/AddWebsite';
import EditWebsite from './components/EditWebsite';

function PrivateRoute({ children }) {
    const { user, loading } = useContext(AuthContext);

    if (loading) {
        return null;
    }

    return user ? children : <Navigate to="/login" replace />;
}

function AppRoutes() {
    return (
        <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route
                path="/"
                element={
                    <PrivateRoute>
                        <WebsiteList />
                    </PrivateRoute>
                }
            />
            <Route
                path="/websites/new"
                element={
                    <PrivateRoute>
                        <AddWebsite />
                    </PrivateRoute>
                }
            />
            <Route
                path="/websites/:id/edit"
                element={
                    <PrivateRoute>
                        <EditWebsite />
                    </PrivateRoute>
                }
            />
            <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
    );
}

function App() {
    return (
        <AuthProvider>
            <Router>
                <Navbar />
                <AppRoutes />
                <ToastContainer position="bottom-right" />
            </Router>
        </AuthProvider>
    );
}

export default App;
