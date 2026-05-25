<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Agreement\StoreAgreementRequest;
use App\Http\Requests\Admin\Agreement\UpdateAgreementRequest;
use App\Http\Resources\Admin\AgreementResource;
use App\Models\System\Agreement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 用户协议
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AgreementController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:agreements.index')->only(['index']);
        $this->middleware('permission:agreements.create')->only(['create', 'store']);
        $this->middleware('permission:agreements.edit')->only(['edit', 'update']);
        $this->middleware('permission:agreements.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $perPage = per_page($request, 15);
            $items = Agreement::query()->orderBy('id')->paginate($perPage);

            return AgreementResource::collection($items);
        }

        return view('admin.agreement.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.agreement.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAgreementRequest $request)
    {
        Agreement::create($request->validated());

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agreement $agreement)
    {
        return view('admin.agreement.edit', [
            'item' => $agreement,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAgreementRequest $request, Agreement $agreement)
    {
        $agreement->update($request->validated());

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agreement $agreement): JsonResponse
    {
        $agreement->delete();

        return $this->success(trans('system.delete_success'));
    }
}
