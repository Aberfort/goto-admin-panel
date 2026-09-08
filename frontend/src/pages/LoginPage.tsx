import { Formik, Form } from 'formik';
import * as Yup from 'yup';
import { useNavigate } from 'react-router-dom';
import { Container, TextField, Button, Typography } from '@mui/material';
import { useAuth } from '../contexts/useAuth';
import { validationErrors } from '../api/errors';

interface FormValues {
    email: string;
    password: string;
}

const initialValues: FormValues = { email: '', password: '' };

const validationSchema = Yup.object({
    email: Yup.string().email('Невірний формат Email').required("Email є обов'язковим"),
    password: Yup.string().required("Пароль є обов'язковим"),
});

function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();

    const onSubmit = async (
        values: FormValues,
        { setSubmitting, setErrors }: { setSubmitting: (v: boolean) => void; setErrors: (e: Record<string, string>) => void }
    ) => {
        try {
            await login(values);
            navigate('/');
        } catch (error) {
            setErrors(validationErrors(error));
            setSubmitting(false);
        }
    };

    return (
        <Container maxWidth="sm" sx={{ mt: 4 }}>
            <Typography variant="h4" gutterBottom>
                Вхід
            </Typography>
            <Formik initialValues={initialValues} validationSchema={validationSchema} onSubmit={onSubmit}>
                {({ isSubmitting, errors, handleChange, touched, values }) => (
                    <Form>
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

                        <Button
                            variant="contained"
                            color="primary"
                            type="submit"
                            disabled={isSubmitting}
                            sx={{ mt: 2 }}
                            fullWidth
                        >
                            Вхід
                        </Button>
                    </Form>
                )}
            </Formik>
        </Container>
    );
}

export default LoginPage;
