<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Report\StoreReportRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Models\Report\Report;

/**
 * 举报控制器
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class ReportController extends Controller
{
    /**
     * ReportController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 提交举报
     */
    public function store(StoreReportRequest $request): ReportResource
    {
        $report = Report::create($request->validated());

        return new ReportResource($report);
    }
}
