import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Formik, Form } from 'formik';
import * as Yup from 'yup';
import { toast } from 'react-toastify';
import { Container, TextField, Button, Typography, Box, Alert } from '@mui/material';
import { getLink, createLink, updateLink } from '../api/links';
import { errorMessage, validationErrors } from '../api/errors';

interface FormValues {
    target_url: string;
    short_code: string;
}

const emptyValues: FormValues = { target_url: '', short_code: '' };

const validationSchema = Yup.object({
    target_url: Yup.string().url('Введіть повний URL, напр. https://example.com').required("URL є обов'язковим"),
    short_code: Yup.string()
        .matches(/^[a-zA-Z0-9_-]*$/, 'Лише латинські літери, цифри, "-" та "_"')
        .max(64),
});

function LinkFormPage() {
    const { siteId, linkId } = useParams<{ siteId?: string; linkId?: string }>();
    const navigate = useNavigate();
    const isEditing = Boolean(linkId);

    const [initialValues, setInitialValues] = useState<FormValues>(emptyValues);
    const [loading, setLoading] = useState(isEditing);
    const [ownerSiteId, setOwnerSiteId] = useState<number | null>(siteId ? Number(siteId) : null);

    useEffect(() => {
        if (!linkId) {
            return;
        }
        getLink(Number(linkId))
            .then((link) => {
                setInitialValues({ target_url: link.target_url, short_code: link.short_code });
                setOwnerSiteId(link.site_id);
            })
            .catch((error) => {
                toast.error(errorMessage(error, 'Помилка при завантаженні посилання.'));
                navigate('/sites');
            })
            .finally(() => setLoading(false));
    }, [linkId, navigate]);

    const onSubmit = async (
        values: FormValues,
        { setSubmitting, setErrors }: { setSubmitting: (v: boolean) => void; setErrors: (e: Record<string, string>) => void }
    ) => {
        try {
            if (isEditing && linkId) {
                await updateLink(Number(linkId), { target_url: values.target_url });
                toast.success('Посилання оновлено.');
            } else if (siteId) {
                await createLink(Number(siteId), {
                    target_url: values.target_url,
                    short_code: values.short_code || undefined,
                });
                toast.success('Посилання додано.');
            }
            navigate(`/sites/${ownerSiteId ?? siteId}/links`);
        } catch (error) {
            setErrors(validationErrors(error));
            toast.error(errorMessage(error, 'Помилка при збереженні посилання.'));
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <Container maxWidth="sm" sx={{ mt: 4 }}>
                <Typography>Завантаження...</Typography>
            </Container>
        );
    }

    return (
        <Container maxWidth="sm" sx={{ mt: 4 }}>
            <Typography variant="h4" gutterBottom>
                {isEditing ? 'Редагувати посилання' : 'Додати посилання'}
            </Typography>

            <Formik
                enableReinitialize
                initialValues={initialValues}
                validationSchema={validationSchema}
                onSubmit={onSubmit}
            >
                {({ isSubmitting, errors, handleChange, touched, values }) => (
                    <Form>
                        <TextField
                            fullWidth
                            margin="normal"
                            label="Цільовий URL"
                            name="target_url"
                            placeholder="https://example.com/target"
                            value={values.target_url}
                            onChange={handleChange}
                            error={touched.target_url && Boolean(errors.target_url)}
                            helperText={touched.target_url && errors.target_url}
                        />

                        {!isEditing && (
                            <Box sx={{ mb: 1 }}>
                                <TextField
                                    fullWidth
                                    margin="normal"
                                    label="Короткий код (необов'язково)"
                                    name="short_code"
                                    placeholder="my-promo"
                                    value={values.short_code}
                                    onChange={handleChange}
                                    error={touched.short_code && Boolean(errors.short_code)}
                                    helperText={touched.short_code && errors.short_code}
                                />
                                <Alert severity="info" sx={{ mt: 1 }}>
                                    Залиш порожнім, щоб код згенерувався автоматично.
                                </Alert>
                            </Box>
                        )}

                        {isEditing && (
                            <Alert severity="info" sx={{ mt: 1, mb: 2 }}>
                                Короткий код не можна змінити після створення — поділені посилання
                                продовжать працювати.
                            </Alert>
                        )}

                        <Button
                            variant="contained"
                            color="primary"
                            type="submit"
                            disabled={isSubmitting}
                            sx={{ mt: 2 }}
                            fullWidth
                        >
                            Зберегти
                        </Button>
                    </Form>
                )}
            </Formik>
        </Container>
    );
}

export default LinkFormPage;
