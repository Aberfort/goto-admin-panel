import { useEffect, useState } from 'react';
import { Link as RouterLink } from 'react-router-dom';
import { Formik, Form } from 'formik';
import * as Yup from 'yup';
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
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    TextField,
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import LinkIcon from '@mui/icons-material/Link';
import BarChartIcon from '@mui/icons-material/BarChart';
import { useAuth } from '../contexts/useAuth';
import { listSites, createSite, updateSite, deleteSite } from '../api/sites';
import { errorMessage, validationErrors } from '../api/errors';
import type { Site } from '../types';

interface FormValues {
    name: string;
    domain: string;
    description: string;
}

const emptyValues: FormValues = { name: '', domain: '', description: '' };

const validationSchema = Yup.object({
    name: Yup.string().required("Назва є обов'язковою").max(255),
    domain: Yup.string().max(255),
    description: Yup.string().max(1000),
});

function SitesPage() {
    const { user } = useAuth();
    const [sites, setSites] = useState<Site[]>([]);
    const [loading, setLoading] = useState(true);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editingSite, setEditingSite] = useState<Site | null>(null);

    const isDemo = Boolean(user?.is_demo);

    useEffect(() => {
        fetchSites();
    }, []);

    const fetchSites = async () => {
        setLoading(true);
        try {
            setSites(await listSites());
        } catch (error) {
            toast.error(errorMessage(error, 'Помилка при завантаженні сайтів.'));
        } finally {
            setLoading(false);
        }
    };

    const openCreateDialog = () => {
        setEditingSite(null);
        setDialogOpen(true);
    };

    const openEditDialog = (site: Site) => {
        setEditingSite(site);
        setDialogOpen(true);
    };

    const handleDelete = async (site: Site) => {
        if (!window.confirm(`Видалити сайт "${site.name}" і всі його посилання?`)) {
            return;
        }
        try {
            await deleteSite(site.id);
            toast.success('Сайт видалено.');
            setSites((prev) => prev.filter((s) => s.id !== site.id));
        } catch (error) {
            toast.error(errorMessage(error, 'Помилка при видаленні сайту.'));
        }
    };

    const onSubmit = async (
        values: FormValues,
        { setSubmitting, setErrors }: { setSubmitting: (v: boolean) => void; setErrors: (e: Record<string, string>) => void }
    ) => {
        try {
            if (editingSite) {
                const updated = await updateSite(editingSite.id, values);
                setSites((prev) => prev.map((s) => (s.id === updated.id ? { ...s, ...updated } : s)));
                toast.success('Сайт оновлено.');
            } else {
                const created = await createSite(values);
                setSites((prev) => [created, ...prev]);
                toast.success('Сайт додано.');
            }
            setDialogOpen(false);
        } catch (error) {
            setErrors(validationErrors(error));
            toast.error(errorMessage(error, 'Помилка при збереженні сайту.'));
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Container maxWidth="lg" sx={{ mt: 4 }}>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                <Typography variant="h4">Сайти</Typography>
                <Button variant="contained" color="primary" onClick={openCreateDialog} disabled={isDemo}>
                    Додати сайт
                </Button>
            </Box>

            {isDemo && (
                <Alert severity="info" sx={{ mb: 2 }}>
                    Демо-акаунт лише для читання — створення, редагування та видалення вимкнені.
                </Alert>
            )}

            {loading ? (
                <Typography>Завантаження...</Typography>
            ) : sites.length === 0 ? (
                <Typography>Сайтів поки немає.</Typography>
            ) : (
                <TableContainer component={Paper}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Назва</TableCell>
                                <TableCell>Домен</TableCell>
                                <TableCell>Опис</TableCell>
                                <TableCell align="right">Посилань</TableCell>
                                <TableCell align="right">Дії</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {sites.map((site) => (
                                <TableRow key={site.id}>
                                    <TableCell>{site.name}</TableCell>
                                    <TableCell>{site.domain}</TableCell>
                                    <TableCell>{site.description}</TableCell>
                                    <TableCell align="right">{site.links_count ?? 0}</TableCell>
                                    <TableCell align="right">
                                        <Tooltip title="Посилання">
                                            <IconButton component={RouterLink} to={`/sites/${site.id}/links`}>
                                                <LinkIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Аналітика">
                                            <IconButton component={RouterLink} to={`/sites/${site.id}/analytics`}>
                                                <BarChartIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Редагувати">
                                            <IconButton onClick={() => openEditDialog(site)} disabled={isDemo}>
                                                <EditIcon fontSize="small" />
                                            </IconButton>
                                        </Tooltip>
                                        <Tooltip title="Видалити">
                                            <IconButton onClick={() => handleDelete(site)} disabled={isDemo}>
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

            <Dialog open={dialogOpen} onClose={() => setDialogOpen(false)} fullWidth maxWidth="sm">
                <Formik
                    enableReinitialize
                    initialValues={
                        editingSite
                            ? {
                                  name: editingSite.name,
                                  domain: editingSite.domain ?? '',
                                  description: editingSite.description ?? '',
                              }
                            : emptyValues
                    }
                    validationSchema={validationSchema}
                    onSubmit={onSubmit}
                >
                    {({ isSubmitting, errors, handleChange, touched, values }) => (
                        <Form>
                            <DialogTitle>{editingSite ? 'Редагувати сайт' : 'Додати сайт'}</DialogTitle>
                            <DialogContent>
                                <TextField
                                    autoFocus
                                    fullWidth
                                    margin="normal"
                                    label="Назва"
                                    name="name"
                                    value={values.name}
                                    onChange={handleChange}
                                    error={touched.name && Boolean(errors.name)}
                                    helperText={touched.name && errors.name}
                                />
                                <TextField
                                    fullWidth
                                    margin="normal"
                                    label="Домен"
                                    name="domain"
                                    placeholder="example.com"
                                    value={values.domain}
                                    onChange={handleChange}
                                    error={touched.domain && Boolean(errors.domain)}
                                    helperText={touched.domain && errors.domain}
                                />
                                <TextField
                                    fullWidth
                                    margin="normal"
                                    label="Опис"
                                    name="description"
                                    multiline
                                    rows={2}
                                    value={values.description}
                                    onChange={handleChange}
                                    error={touched.description && Boolean(errors.description)}
                                    helperText={touched.description && errors.description}
                                />
                            </DialogContent>
                            <DialogActions>
                                <Button onClick={() => setDialogOpen(false)}>Скасувати</Button>
                                <Button type="submit" variant="contained" disabled={isSubmitting}>
                                    Зберегти
                                </Button>
                            </DialogActions>
                        </Form>
                    )}
                </Formik>
            </Dialog>
        </Container>
    );
}

export default SitesPage;
