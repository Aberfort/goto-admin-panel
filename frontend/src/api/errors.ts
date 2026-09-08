import { AxiosError } from 'axios';
import type { ApiErrorPayload } from '../types';

export function errorMessage(error: unknown, fallback: string): string {
    if (error instanceof AxiosError) {
        const payload = error.response?.data as ApiErrorPayload | undefined;

        return payload?.message ?? fallback;
    }

    return fallback;
}

export function validationErrors(error: unknown): Record<string, string> {
    if (error instanceof AxiosError && error.response?.status === 422) {
        const payload = error.response.data as ApiErrorPayload;
        const errors: Record<string, string> = {};

        for (const [field, messages] of Object.entries(payload.errors ?? {})) {
            errors[field] = messages[0];
        }

        return errors;
    }

    return {};
}
