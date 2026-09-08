import { useCallback, useEffect, useState } from 'react';
import { Link as RouterLink, useParams } from 'react-router-dom';
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
    Alert,
    Switch,
    Link,
    Breadcrumbs,
    Chip,
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import BarChartIcon from '@mui/icons-material/BarChart';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';
import { useAuth } from '../contexts/useAuth';
import { getSite } from '../api/sites';
import { listLinks, deleteLink, toggleLink } from '../api/links';
import { errorMessage } from '../api/errors';
import type { Site, Link as LinkType } from '../types';

const API_URL = import.meta.env.VITE_API_URL || window.location.origin;

function SiteLinksPage() {
    const { siteId } = useParams<{ siteId: string }>();
    const { user } = useAuth();
    const [site, setSite] = useState<Site | null>(null);
    const [links, setLinks] = useState<LinkType[]>([]);
    const [loading, setLoading] = useState(true);

    const isDemo = Boolean(user?.is_demo);
    const id = Number(siteId);

    const fetchData = useCallback(async () => {
        setLoading(true);
        try {
            const [siteData, linksData] = await Promise.all([getSite(id), listLinks(id)]);
            setSite(siteData);
            setLinks(linksData);
        } catch (error) {
            toast.error(errorMessage(error, 'Помилка при завантаженні посилань.'));
        } finally {
            setLoading(false);
        }
    }, [id]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const handleCopy = async (shortCode: string) => {
        try {
            await navigator.clipboard.writeText(`${API_URL}/r/${shortCode}`);
            toast.success('Посилання скопійовано.');
        } catch {
            toast.error('Не вдалося скопіювати посилання.');
        }
    };

    const handleToggle = async (link: LinkType) => {
        try {
            const updated = await toggleLink(link.id);
            setLinks((prev) => prev.map((l) => (l.id === link.id ? updated : l)));
        } catch (error) {
            toast.error(errorMessage(error, 'Помилка при зміні статусу.'));
        }
    };

    const handleDelete = async (link: LinkType) => {
        if (!window.confirm('Видалити це посилання?')) {
            return;
        }
        try {
            await deleteLink(link.id);
            toast.success('Посилання видалено.');
            setLinks((prev) => prev.filter((l) => l.id !== link.id));
        } catch (error) {
            toast.error(errorMessage(error, 'Помилка при видаленні посилання.'));
        }
    };

    return (
        <Container maxWidth="lg" sx={{ mt: 4 }}>
            <Breadcrumbs sx={{ mb: 2 }}>
                <Link component={RouterLink} to="/sites" underline="hover">
                    Сайти
                </Link>
                <Typography color="text.primary">{site?.name ?? '...'}</Typography>
            </Breadcrumbs>

            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                <Typography variant="h4">Посилання{site ? `: ${site.name}` : ''}</Typography>
                <Button
                    variant="contained"
                    color="primary"
                    component={RouterLink}
                    to={`/sites/${id}/links/new`}
                    disabled={isDemo}
                >
                    Додати посилання
                </Button>
            </Box>

            {isDemo && (
                <Alert severity="info" sx={{ mb: 2 }}>
                    Демо-акаунт лише для читання — створення, редагування та видалення вимкнені.
                </Alert>
            )}

            {loading ? (
                <Typography>Завантаження...</Typography>
            ) : links.length === 0 ? (
                <Typography>Посилань поки немає.</Typography>
            ) : (
                <TableContainer component={Paper}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Коротке посилання</TableCell>
                                <TableCell>Ціль</TableCell>
                                <TableCell align="right">Кліки</TableCell>
                                <TableCell align="center">Активне</TableCell>
                                <TableCell align="right">Дії</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {links.map((link) => (
                                <TableRow key={link.id}>
                                    <TableCell>
                                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                                            <code>/r/{link.short_code}</code>
                                            <Tooltip title="Копіювати">
                                                <IconButton size="small" onClick={() => handleCopy(link.short_code)}>
                                                    <ContentCopyIcon fontSize="inherit" />
                                                </IconButton>
                                            </Tooltip>
                                        </Box>
                                    </TableCell>
                                    <TableCell
                                        sx={{
                                            maxWidth: 320,
                                            overflow: 'hidden',
                                            textOverflow: 'ellipsis',
                                            whiteSpace: 'nowrap',
                                        }}
                                    >
                                        {link.target_url}
                                    </TableCell>
                                    <TableCell align="right">{link.clicks_count}</TableCell>
                                    <TableCell align="center">
                                        {isDemo ? (
                                            <Chip
                                                size="small"
                                                label={link.is_active ? 'Активне' : 'Вимкнене'}
                                                color={link.is_active ? 'success' : 'default'}
                                            />
                                        ) : (
                                            <Switch
                                                checked={link.is_active}
                                                onChange={() => handleToggle(link)}
                                                size="small"
                                            />
                                        )}
                                    </TableCell>
                                    <TableCell align="right">
                                        <Tooltip title="Аналітика">
                                            <IconButton component={RouterLink} to={`/links/${link.id}/analytics`}>
                                                <BarChartIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Редагувати">
                                            <IconButton
                                                component={RouterLink}
                                                to={`/links/${link.id}/edit`}
                                                disabled={isDemo}
                                            >
                                                <EditIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Видалити">
                                            <IconButton onClick={() => handleDelete(link)} disabled={isDemo}>
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

export default SiteLinksPage;
