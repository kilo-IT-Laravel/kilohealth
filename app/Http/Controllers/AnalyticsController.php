<?php

namespace App\Http\Controllers;

use App\Repositories\Dashboard\dashbInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $Repository;

    public function __construct(dashbInterface $repository)
    {
        $this->Repository = $repository;
    }

    public function getDashboardAnalytics(): JsonResponse
    {
        try {
            $data = [
                'user_growth' => $this->Repository->getUserGrowth(),
                'content_overview' => $this->Repository->getContentOverview(),
                'engagement_summary' => $this->Repository->getEngagementSummary(),
                'top_performing_post' => $this->Repository->getTopPerformingPost(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Successfully',
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'Success' => false,
                'message' => 'Internal server errors',
            ], 500);
        }
    }
}
