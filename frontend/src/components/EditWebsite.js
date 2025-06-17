import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Formik, Form } from 'formik';
import * as Yup from 'yup';
import api from '../services/api';
import {
    Container,
    TextField,
    Button,
    Typography,
    Box,
    Alert,
} from '@mui/material';
import { toast } from 'react-toastify';

function EditWebsite() {
    const { id } = useParams();
    const navigate = useNavigate();

    const [initialValues, setInitialValues] = useState({
        name: '',
        reg_url: '',
        front_url: '',
        app_url: '',
    });
    const [loading, setLoading] = useState(true);

    const [focusField, setFocusField] = useState('');

    useEffect(() => {
        fetchWebsite();
    }, []);

    const fetchWebsite = async () => {
        try {
            const response = await api.get(`/api/websites/${id}`);
            const { name, reg_url, front_url, app_url } = response.data;
            setInitialValues({ name, reg_url, front_url, app_url });
            setLoading(false);
        } catch (error) {
            toast.error('Помилка при завантаженні даних вебсайту!');
            navigate('/');
        }
    };

    // Схема валідації
    const validationSchema = Yup.object({
        name: Yup.string()
            .required("Назва є обов'язковою")
            .matches(
                /^https:\/\/((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,6}(\:[0-9]+)?(\/.*)?$/,
                "Невірний формат. Приклад: https://example.com"
            ),

        reg_url: Yup.string()
            .required("Reg URL є обов'язковим")
            .matches(
                /^https:\/\/((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,6}(\:[0-9]+)?(\/.*)?$/,
                "Невірний формат. Приклад: https://example.com/reg"
            ),

        front_url: Yup.string()
            .required("Front URL є обов'язковим")
            .matches(
                /^https:\/\/((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,6}(\:[0-9]+)?(\/.*)?$/,
                "Невірний формат. Приклад: https://example.com/front"
            ),

        app_url: Yup.string()
            .required("App URL є обов'язковим")
            .matches(
                /^https:\/\/((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,6}(\:[0-9]+)?(\/.*)?$/,
                "Невірний формат. Приклад: https://example.com/app"
            ),
    });

    const onSubmit = async (values, { setSubmitting, setErrors }) => {
        try {
            await api.put(`/api/websites/${id}`, values);
            toast.success('Вебсайт успішно оновлено!');
            navigate('/');
        } catch (error) {
            if (error.response) {
                if (error.response.status === 500) {
                    toast.error('Помилка при додаванні сайту.');
                } else if (error.response.status === 422) {
                    const validationErrors = error.response.data.errors;
                    const formErrors = {};
                    for (const key in validationErrors) {
                        formErrors[key] = validationErrors[key][0];
                    }
                    setErrors(formErrors);
                } else {
                    toast.error(
                        `Помилка при додаванні сайту: ${
                            error.response.data.message || 'Невідома помилка.'
                        }`
                    );
                }
            } else {
                toast.error('Помилка при додаванні сайту.');
            }
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <Container maxWidth="sm" sx={{ mt: 4 }}>
                <Typography variant="h5">Завантаження...</Typography>
            </Container>
        );
    }

    return (
        <Container maxWidth="sm" sx={{ mt: 4 }}>
            <Typography variant="h4" gutterBottom>
                Редагувати Вебсайт
            </Typography>

            <Formik
                enableReinitialize
                initialValues={initialValues}
                validationSchema={validationSchema}
                onSubmit={onSubmit}
            >
                {({ isSubmitting, errors, handleChange, touched, values }) => (
                    <Form>
                        {/* Поле name з Regex-валідацією */}
                        <Box sx={{ position: 'relative', mb: 2 }}>
                            <TextField
                                fullWidth
                                label="Site"
                                name="name"
                                value={values.name}
                                onChange={handleChange}
                                error={touched.name && Boolean(errors.name)}
                                helperText={touched.name && errors.name}
                                onFocus={() => setFocusField('name')}
                                onBlur={() => setFocusField('')}
                            />
                            {focusField === 'name' && (
                                <Alert severity="info" sx={{ mt: 1 }}>
                                    <b>Підказка:</b> Назва сайту має починатися з{" "}
                                    <code>https://</code> та бути валідним доменом (наприклад: <i>https://example.com</i>).
                                </Alert>
                            )}
                        </Box>

                        {/* Reg URL */}
                        <Box sx={{ position: 'relative', mb: 2 }}>
                            <TextField
                                fullWidth
                                label="Reg URL"
                                name="reg_url"
                                value={values.reg_url}
                                onChange={handleChange}
                                error={touched.reg_url && Boolean(errors.reg_url)}
                                helperText={touched.reg_url && errors.reg_url}
                                onFocus={() => setFocusField('reg_url')}
                                onBlur={() => setFocusField('')}
                            />
                            {focusField === 'reg_url' && (
                                <Alert severity="warning" sx={{ mt: 1 }}>
                                    <b>Увага!</b> Переконайтесь, що посилання вірне та підтримує протокол <i>https://</i>.
                                </Alert>
                            )}
                        </Box>

                        {/* Front URL */}
                        <Box sx={{ position: 'relative', mb: 2 }}>
                            <TextField
                                fullWidth
                                label="Front URL"
                                name="front_url"
                                value={values.front_url}
                                onChange={handleChange}
                                error={touched.front_url && Boolean(errors.front_url)}
                                helperText={touched.front_url && errors.front_url}
                                onFocus={() => setFocusField('front_url')}
                                onBlur={() => setFocusField('')}
                            />
                            {focusField === 'front_url' && (
                                <Alert severity="warning" sx={{ mt: 1 }}>
                                    <b>Увага!</b> Переконайтесь, що посилання вірне та підтримує протокол <i>https://</i>.
                                </Alert>
                            )}
                        </Box>

                        {/* App URL */}
                        <Box sx={{ position: 'relative', mb: 2 }}>
                            <TextField
                                fullWidth
                                label="App URL"
                                name="app_url"
                                value={values.app_url}
                                onChange={handleChange}
                                error={touched.app_url && Boolean(errors.app_url)}
                                helperText={touched.app_url && errors.app_url}
                                onFocus={() => setFocusField('app_url')}
                                onBlur={() => setFocusField('')}
                            />
                            {focusField === 'app_url' && (
                                <Alert severity="warning" sx={{ mt: 1 }}>
                                    <b>Увага!</b> Переконайтесь, що посилання вірне та підтримує протокол <i>https://</i>.
                                </Alert>
                            )}
                        </Box>

                        <Button
                            variant="contained"
                            color="primary"
                            type="submit"
                            disabled={isSubmitting}
                            sx={{ mt: 2 }}
                            fullWidth
                        >
                            Оновити
                        </Button>
                    </Form>
                )}
            </Formik>
        </Container>
    );
}

export default EditWebsite;
