<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('settings/Ai', [
            'applicant_profile' => auth()->user()->getSetting('applicant_profile', ''),
            'cover_letter_prompt' => auth()->user()->getSetting('cover_letter_prompt', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'applicant_profile' => ['max:2000'],
            'cover_letter_prompt' => ['max:3000'],
        ]);

        $request->user()->setSetting('applicant_profile', $validated['applicant_profile']);
        $request->user()->setSetting('cover_letter_prompt', $validated['cover_letter_prompt']);

        return back();
    }
}
