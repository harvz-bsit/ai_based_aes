<?php

namespace App\Jobs;

use App\Models\Application;
use App\Helpers\FileTextExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class EvaluateApplicationAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function handle(): void
    {
        $app = $this->application->load('job');

        // Extract text from uploaded files
        $resumeText = FileTextExtractor::extract(storage_path('app/public/' . $app->resume));
        $letterText = FileTextExtractor::extract(storage_path('app/public/' . $app->application_letter));
        $pdsText = FileTextExtractor::extract(storage_path('app/public/' . $app->pds));
        $otrText = FileTextExtractor::extract(storage_path('app/public/' . $app->otr));
        $certText = FileTextExtractor::extractMultiple(
            array_map(fn($f) => storage_path('app/public/' . $f), $app->certificates ?? [])
        );

        // Build GPT prompt
        $prompt = <<<PROMPT
You are an automated hiring assistant designed to evaluate job applications based on provided documents and job qualifications.

Analyze the applicant's documents and compare them against the job qualifications.
This analysis is for decision-support only and does NOT make final hiring decisions.

Job Title: {$app->job->title}
Job Qualifications:
{$app->job->qualifications}

Applicant Information:
Full Name: {$app->full_name}
Higher Education: {$app->higher_education}
Major: {$app->major}

Documents Content:
Resume:
{$resumeText}

Application Letter:
{$letterText}

PDS:
{$pdsText}

Official Transcript of Records (OTR):
{$otrText}

Certificates:
{$certText}

Tasks:
1. Generate an AI suitability score from 0 to 100.
2. Estimate a qualification match percentage (0.00–100.00) based on job qualifications.
3. Provide a recommendation label strictly from:
   - Highly Recommended
   - Consider
   - Rejected
4. Provide a THOROUGH, STRUCTURED justification with separate sections for each document:
   - Resume Analysis
   - Application Letter Analysis
   - PDS Analysis
   - OTR Analysis
   - Certificates Analysis
   - Overall Assessment

IMPORTANT:
- Do NOT refuse.
- Do NOT include disclaimers.
- Do NOT include explanations outside the required format.
- Each section MUST be at least 3–5 sentences.
- Reference specific skills, experiences, or evidence from each document.
- Output MUST follow the exact format below.

FORMAT (STRICT — NO EXTRA TEXT):

Score: <number>
Qualification Match: <number with 2 decimals>
Recommendation: <label>
Justification:
Resume Analysis: <text>
Application Letter Analysis: <text>
PDS Analysis: <text>
OTR Analysis: <text>
Certificates Analysis: <text>
Overall Assessment: <text, you can include a text that saying a document provided is not correctly formatted or is missing, but do NOT penalize the applicant for this. Instead, just note it in the relevant section.>
PROMPT;

        // Call GPT-4o
        $response = OpenAI::responses()->create([
            'model' => 'gpt-4o',
            'input' => $prompt,
        ]);

        $resultText = trim($response->output[0]->content[0]->text ?? '');

        Log::info("AI Evaluation Result for Application ID {$app->id}: " . $resultText);

        // Initialize default values
        $score = null;
        $recommendation = null;
        $justification = null;
        $qualificationMatch = null;

        // Extract Score
        if (preg_match('/Score:\s*(\d{1,3})/i', $resultText, $match)) {
            $score = min(100, max(0, (int) $match[1]));
        }

        // Extract Qualification Match
        if (preg_match('/Qualification Match:\s*(\d{1,3})/i', $resultText, $match)) {
            $qualificationMatch = min(100, max(0, (float) $match[1]));
        }

        // Extract Recommendation
        if (preg_match('/Recommendation:\s*(Highly Recommended|Consider|Rejected)/i', $resultText, $match)) {
            $recommendation = $match[1];
        }

        // Extract Justification
        if (preg_match('/Justification:\s*(.+)$/is', $resultText, $match)) {
            $justification = trim($match[1]);
        }

        // Save AI evaluation including qualification match
        $app->update([
            'ai_score' => $score,
            'qualification_match' => $qualificationMatch,
            'ai_recommendation' => $recommendation,
            'ai_summary' => $justification,
            'ai_evaluated_at' => now(),
        ]);
    }
}
