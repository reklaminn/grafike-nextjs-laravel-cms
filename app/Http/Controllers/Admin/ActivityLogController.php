<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($subjectType = $request->input('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        if ($logName = $request->input('log_name')) {
            $query->where('log_name', $logName);
        }

        if ($causerId = $request->input('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $activities = $query->paginate(50)->withQueryString();

        $subjectTypes = Activity::distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(fn($type) => class_basename($type));

        $logNames = Activity::distinct()
            ->whereNotNull('log_name')
            ->pluck('log_name');

        $admins = \App\Models\Admin::orderBy('name')->get(['id', 'name']);

        return view('admin.activity-log.index', compact('activities', 'subjectTypes', 'logNames', 'admins'));
    }
}
