<?php

namespace App\Http\Controllers;

use App\Actions\RecordLinkClick;
use App\Models\Link;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    public function go(string $code, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        $link = Link::where('short_code', $code)->where('is_active', true)->first();

        if (! $link) {
            abort(Response::HTTP_NOT_FOUND);
        }

        // 410 rather than 404: the link did exist, the owner set it to stop
        // working, and that's a meaningfully different answer for anyone
        // (or anything) following it.
        if ($link->isExpired()) {
            return response()->view('links.gate', [
                'mode' => 'expired',
                'code' => $code,
            ], Response::HTTP_GONE);
        }

        if ($link->isPasswordProtected()) {
            return view('links.gate', ['mode' => 'password', 'code' => $code, 'error' => null]);
        }

        $recordLinkClick->handle($link, $request);

        return redirect()->away($link->target_url, Response::HTTP_FOUND);
    }

    public function unlock(string $code, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        $link = Link::where('short_code', $code)->where('is_active', true)->first();

        if (! $link || ! $link->isPasswordProtected()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        if ($link->isExpired()) {
            return response()->view('links.gate', [
                'mode' => 'expired',
                'code' => $code,
            ], Response::HTTP_GONE);
        }

        if (! Hash::check((string) $request->input('password'), $link->password)) {
            return response()->view('links.gate', [
                'mode' => 'password',
                'code' => $code,
                'error' => 'Невірний пароль.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Only counts once the visitor is actually let through - showing
        // the password form isn't a click.
        $recordLinkClick->handle($link, $request);

        return redirect()->away($link->target_url, Response::HTTP_FOUND);
    }
}
