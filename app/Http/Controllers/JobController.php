<?php

namespace App\Http\Controllers;

use App\Models\UpworkJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JobController extends Controller
{
    public function coverLetter(Request $request, string $id)
    {
        $user = $request->user();
        $job = UpworkJob::with('category')->findOrFail($id);

        $coverLetterPrompt = trim((string) $user->getSetting('cover_letter_prompt', ''));
        $applicantProfile = trim((string) $user->getSetting('applicant_profile', ''));

        $error = $coverLetterPrompt === '' || $applicantProfile === '';

        $jobContext = "Title: {$job->title}\nDescription: {$job->description}";

        $search = ['{{applicant_profile}}', '{{job_details}}'];
        $replace = [$applicantProfile, $jobContext];
        $composedPrompt = str_replace($search, $replace, $coverLetterPrompt);

        return Inertia::render('CoverLetter', [
            'composedPrompt' => $composedPrompt,
            'error' => $error,
        ]);

    }
}
