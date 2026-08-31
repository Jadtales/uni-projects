<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ShortLink;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request, string $code)
    {
        $shortLink = ShortLink::query()
            ->where('short_code', $code)
            ->first();

        if (!$shortLink) {
            return response('Short link not found.', 404);
        }

        $shortLink->increment('click_count');

        return redirect()->away($shortLink->original_url, 302);
    }
}
