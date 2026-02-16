<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\KMeansService;
use App\Models\Application;

class KMeansController extends Controller
{
    public function index()
    {
        $applications = Application::with('job')->get();
        $kMeans = new KMeansService();

        // Now this is CONTEXT-AWARE clustering
        $clusters = $kMeans->clusterApplicants(3);

        $chartData = $applications->map(function ($app) use ($clusters) {
            return [
                // X-axis = Job index (not score anymore)
                'x' => $app->job_id,

                // Y-axis = Qualification match
                'y' => $app->qualification_match ?? 0,

                // Tooltip info
                'label' => $app->full_name,
                'position' => $app->job->title ?? 'N/A',
                'campus' => $app->job->campus ?? 'N/A',
                'department' => $app->job->department ?? 'N/A',
                'ai_score' => $app->ai_score ?? 0,
                'recommendation' => $app->ai_recommendation ?? 'N/A',

                // Real intelligent cluster
                'cluster' => $clusters[$app->id] ?? 0
            ];
        });

        return view('admin.clusters', compact(
            'applications',
            'clusters',
            'chartData'
        ));
    }
}
