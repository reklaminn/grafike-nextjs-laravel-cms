<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\FrontendRevalidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PageUnlockController extends Controller
{
    public function unlock(Request $request, Page $page)
    {
        $request->validate([
            'page_password' => 'required|string',
        ]);

        if (! $page->is_password_protected) {
            return back();
        }

        $password = $request->input('page_password');

        // Check password (support both plain text legacy and hashed)
        $valid = false;
        if (Hash::needsRehash($page->page_password)) {
            // Legacy plain text password
            $valid = $password === $page->page_password;
            if ($valid) {
                // Upgrade to hashed password
                $page->update(['page_password' => Hash::make($password)]);
            }
        } else {
            $valid = Hash::check($password, $page->page_password);
        }

        if ($valid) {
            // Store unlocked page in session
            $unlocked = session('unlocked_pages', []);
            $unlocked[] = $page->id;
            session(['unlocked_pages' => array_unique($unlocked)]);

            // Bust Next.js ISR cache so the now-unlocked page is re-fetched
            try {
                $locale = app()->getLocale();
                app(FrontendRevalidator::class)->flush(
                    ["/{$locale}/{$page->slug}"],
                    ["page-{$page->slug}", 'pages'],
                );
            } catch (\Throwable) {
                // Non-fatal — page will be served fresh on next ISR cycle
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'Sayfa kilidi açıldı.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Girdiğiniz şifre yanlış.'], 422);
        }

        return back()->with('error', 'Girdiğiniz şifre yanlış.');
    }
}
