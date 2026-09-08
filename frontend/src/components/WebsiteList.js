import React, { useEffect, useState } from 'react';
import { Link as RouterLink } from 'react-router-dom';
import api from '../services/api';
import { toast } from 'react-toastify';
import {
    Container,
    Typography,
    Table,
    TableHead,
    TableBody,
    TableRow,
    TableCell,
    TableContainer,
    Paper,
    Button,
    IconButton,
    Tooltip,
    Box,
    Link,
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import RefreshIcon from '@mui/icons-material/Refresh';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';

function WebsiteList() {
    const [websites, setWebsites] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchWebsites();
    }, []);

    const fetchWebsites = async () => {
        setLoading(true);
        try {
            const response = await api.get('/api/websites');
            setWebsites(response.data.data ?? response.data);
        } catch (error) {
            toast.error('Помилка при завантаженні списку вебсайтів.');
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        if (!window.confirm('Видалити цей вебсайт?')) {
            return;
        }
        try {
            await api.delete(`/api/websites/${id}`);
            toast.success('Вебсайт видалено.');
            setWebsites((prev) => prev.filter((w) => w.id !== id));
        } catch (error) {
            toast.error('Помилка при видаленні вебсайту.');
        }
    };

    const handleRegenerateToken = async (id) => {
        try {
            const response = await api.post(`/api/websites/${id}/regenerate-token`);
            toast.success('API токен перегенеровано.');
            setWebsites((prev) =>
                prev.map((w) => (w.id === id ? { ...w, api_token: response.data.website.api_token } : w))
            );
        } catch (error) {
            toast.error('Помилка при перегенерації токену.');
        }
    };

    const handleCopyToken = async (token) => {
        try {
            await navigator.clipboard.writeText(token);
            toast.success('Токен скопійовано.');
        } catch (error) {
            toast.error('Не вдалося скопіювати токен.');
        }
    };

    return (
        <Container maxWidth="lg" sx={{ mt: 4 }}>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                <Typography variant="h4">Вебсайти</Typography>
                <Button variant="contained" color="primary" component={RouterLink} to="/websites/new">
                    Додати вебсайт
                </Button>
            </Box>

            {loading ? (
                <Typography>Завантаження...</Typography>
            ) : websites.length === 0 ? (
                <Typography>Вебсайтів поки немає.</Typography>
            ) : (
                <TableContainer component={Paper}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Назва</TableCell>
                                <TableCell>Reg URL</TableCell>
                                <TableCell>Front URL</TableCell>
                                <TableCell>App URL</TableCell>
                                <TableCell>API токен</TableCell>
                                <TableCell align="right">Дії</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {websites.map((website) => (
                                <TableRow key={website.id}>
                                    <TableCell>
                                        <Link href={website.name} target="_blank" rel="noopener noreferrer">
                                            {website.name}
                                        </Link>
                                    </TableCell>
                                    <TableCell>{website.reg_url}</TableCell>
                                    <TableCell>{website.front_url}</TableCell>
                                    <TableCell>{website.app_url}</TableCell>
                                    <TableCell sx={{ maxWidth: 160, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                        {website.api_token}
                                    </TableCell>
                                    <TableCell align="right">
                                        <Tooltip title="Копіювати токен">
                                            <IconButton onClick={() => handleCopyToken(website.api_token)}>
                                                <ContentCopyIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Перегенерувати токен">
                                            <IconButton onClick={() => handleRegenerateToken(website.id)}>
                                                <RefreshIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Редагувати">
                                            <IconButton component={RouterLink} to={`/websites/${website.id}/edit`}>
                                                <EditIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Видалити">
                                            <IconButton onClick={() => handleDelete(website.id)}>
                                                <DeleteIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </TableContainer>
            )}
        </Container>
    );
}

export default WebsiteList;
