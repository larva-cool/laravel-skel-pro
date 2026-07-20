<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Http\Requests\Admin\Report\HandleReportRequest;
use App\Http\Resources\Admin\ReportResource;
use App\Models\Report\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 举报管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class ReportController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:reports.index')->only(['index']);
        $this->middleware('permission:reports.edit')->only(['edit', 'update']);
        $this->middleware('permission:reports.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = Report::query()->with('user');

            if ($status = $request->string('status')->toString()) {
                $query->where('status', $status);
            }
            if ($reason = $request->string('reason')->toString()) {
                $query->where('reason', $reason);
            }
            if ($reportableType = $request->string('reportable_type')->toString()) {
                $query->where('reportable_type', $reportableType);
            }

            $items = $query->orderByDesc('id')->paginate(per_page($request));

            return ReportResource::collection($items);
        }

        return view('admin.report.index', [
            'reason_options' => ReportReason::options(),
            'status_options' => ReportStatus::options(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        $report->load('user');

        return view('admin.report.edit', [
            'item' => $report,
            'status_options' => ReportStatus::options(),
            'update_url' => route('admin.reports.update', $report->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HandleReportRequest $request, Report $report): JsonResponse
    {
        $validated = $request->validated();
        $report->handleByAdmin(
            $request->user('admin'),
            ReportStatus::from($validated['status']),
            $validated['remark'] ?? null,
        );

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report): JsonResponse
    {
        $report->delete();

        return $this->success(trans('system.delete_success'));
    }
}
