import React, { useContext } from 'react';
import { Formik, Form } from 'formik';
import * as Yup from 'yup';
import { AuthContext } from '../contexts/AuthContext';
import { toast } from 'react-toastify';
import { useNavigate } from 'react-router-dom';
import {
    Container,
    TextField,
    Button,
    Typography,
} from '@mui/material';

function Register() {
    const { register } = useContext(AuthContext);
    const navigate = useNavigate();

    const initialValues = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    };

    const validationSchema = Yup.object({
        name: Yup.string().required("Ім'я є обов'язковим"),
        email: Yup.string().email('Невірний формат Email').required("Email є обов'язковим"),
        password: Yup.string().min(8, 'Пароль має містити щонайменше 8 символів').required("Пароль є обов'язковим"),
        password_confirmation: Yup.string()
            .oneOf([Yup.ref('password'), null], 'Паролі повинні співпадати')
            .required("Підтвердження пароля є обов'язковим"),
    });

    const onSubmit = async (values, { setSubmitting, setErrors }) => {
        try {
            await register(values);
            navigate('/login');
        } catch (error) {
            if (error.response && error.response.status === 422) {
                const validationErrors = error.response.data.errors;
                toast.error('Помилки валідації.');
                setErrors(validationErrors);
            } else {
                toast.error('Помилка при реєстрації.');
            }
            setSubmitting(false);
        }
    };

    return (
        <Container maxWidth="sm" sx={{ mt: 4 }}>
            <Typography variant="h4" gutterBottom>
                Реєстрація
            </Typography>
            <Formik
                initialValues={initialValues}
                validationSchema={validationSchema}
                onSubmit={onSubmit}
            >
                {({ isSubmitting, errors, handleChange, touched, values }) => (
                    <Form>
                        <TextField
                            fullWidth
                            margin="normal"
                            label="Ім'я"
                            name="name"
                            value={values.name}
                            onChange={handleChange}
                            error={touched.name && Boolean(errors.name)}
                            helperText={touched.name && errors.name}
                        />

                        <TextField
                            fullWidth
                            margin="normal"
                            label="Email"
                            name="email"
                            type="email"
                            value={values.email}
                            onChange={handleChange}
                            error={touched.email && Boolean(errors.email)}
                            helperText={touched.email && errors.email}
                        />

                        <TextField
                            fullWidth
                            margin="normal"
                            label="Пароль"
                            name="password"
                            type="password"
                            value={values.password}
                            onChange={handleChange}
                            error={touched.password && Boolean(errors.password)}
                            helperText={touched.password && errors.password}
                        />

                        <TextField
                            fullWidth
                            margin="normal"
                            label="Підтвердження пароля"
                            name="password_confirmation"
                            type="password"
                            value={values.password_confirmation}
                            onChange={handleChange}
                            error={touched.password_confirmation && Boolean(errors.password_confirmation)}
                            helperText={touched.password_confirmation && errors.password_confirmation}
                        />

                        <Button
                            variant="contained"
                            color="primary"
                            type="submit"
                            disabled={isSubmitting}
                            sx={{ mt: 2 }}
                            fullWidth
                        >
                            Реєстрація
                        </Button>
                    </Form>
                )}
            </Formik>
        </Container>
    );
}

export default Register;
