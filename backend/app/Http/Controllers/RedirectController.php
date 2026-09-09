<?php

namespace App\Http\Controllers;

use App\Actions\RecordLinkClick;
use App\Models\Domain;
use App\Models\Link;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    /** The app's own /r/{code} route - one namespace shared by every site. */
    public function go(string $code, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        return $this->resolve($this->findGlobally($code), $code, "/r/{$code}", $request, $recordLinkClick);
    }

    public function unlock(string $code, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        return $this->submitPassword($this->findGlobally($code), $code, "/r/{$code}", $request, $recordLinkClick);
    }

    /**
     * A verified custom domain serving <host>/{code}. Scoped to that
     * domain's own site, so a branded host never resolves someone else's
     * codes.
     */
    public function goOnDomain(string $code, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        return $this->resolve($this->findOnHost($request->getHost(), $code), $code, "/{$code}", $request, $recordLinkClick);
    }

    public function unlockOnDomain(string $code, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        return $this->submitPassword($this->findOnHost($request->getHost(), $code), $code, "/{$code}", $request, $recordLinkClick);
    }

    private function findGlobally(string $code): ?Link
    {
        return Link::where('short_code', $code)->where('is_active', true)->first();
    }

    private function findOnHost(string $host, string $code): ?Link
    {
        $domain = Domain::where('host', strtolower($host))->whereNotNull('verified_at')->first();

        if (! $domain) {
            return null;
        }

        return $domain->site->links()->where('short_code', $code)->where('is_active', true)->first();
    }

    private function resolve(?Link $link, string $code, string $action, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        if (! $link) {
            abort(Response::HTTP_NOT_FOUND);
        }

        // 410 rather than 404: the link did exist, the owner set it to stop
        // working, and that's a meaningfully different answer for anyone
        // (or anything) following it.
        if ($link->isExpired()) {
            return $this->expiredView();
        }

        if ($link->isPasswordProtected()) {
            return view('links.gate', [
                'mode' => 'password',
                'action' => $action,
                'error' => null,
            ]);
        }

        $recordLinkClick->handle($link, $request);

        return redirect()->away($link->target_url, Response::HTTP_FOUND);
    }

    private function submitPassword(?Link $link, string $code, string $action, Request $request, RecordLinkClick $recordLinkClick): Response|View
    {
        if (! $link || ! $link->isPasswordProtected()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        if ($link->isExpired()) {
            return $this->expiredView();
        }

        if (! Hash::check((string) $request->input('password'), $link->password)) {
            return response()->view('links.gate', [
                'mode' => 'password',
                'action' => $action,
                'error' => 'Невірний пароль.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Only counts once the visitor is actually let through - showing
        // the password form isn't a click.
        $recordLinkClick->handle($link, $request);

        return redirect()->away($link->target_url, Response::HTTP_FOUND);
    }

    private function expiredView(): Response
    {
        return response()->view('links.gate', [
            'mode' => 'expired',
            'action' => null,
            'error' => null,
        ], Response::HTTP_GONE);
    }
}
