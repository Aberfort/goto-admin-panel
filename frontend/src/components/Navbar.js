import React, { useContext } from 'react';
import { AuthContext } from '../contexts/AuthContext';
import { Link as RouterLink, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
    AppBar,
    Toolbar,
    Typography,
    Button,
} from '@mui/material';

function Navbar() {
    const { user, logout, loading } = useContext(AuthContext);
    const navigate = useNavigate();

    const handleLogout = async () => {
        try {
            await logout();
            toast.success('Вихід успішний!');
            navigate('/login');
        } catch (error) {
            toast.error('Помилка при виході.');
        }
    };

    return (
        <AppBar position="static">
            <Toolbar>
                <Typography variant="h6" component={RouterLink} to="/" sx={{ flexGrow: 1, color: 'inherit', textDecoration: 'none' }}>
                    GoTo
                </Typography>

                {loading ? (
                    <Typography variant="body1">Завантаження...</Typography>
                ) : user ? (
                    <>
                        <Typography variant="body1" sx={{ mr: 2 }}>
                            Привіт, {user.name}!
                        </Typography>
                        <Button color="inherit" onClick={handleLogout}>
                            Вийти
                        </Button>
                    </>
                ) : (
                    <>
                        <Button color="inherit" component={RouterLink} to="/login">
                            Вхід
                        </Button>
                    </>
                )}
            </Toolbar>
        </AppBar>
    );
}

export default Navbar;
