<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\PageResource;
use App\Models\System\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 页面管理
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class PageController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:pages.index')->only(['index']);
        $this->middleware('permission:pages.create')->only(['create', 'store']);
        $this->middleware('permission:pages.edit')->only(['edit', 'update']);
        $this->middleware('permission:pages.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $items = Page::query()->orderByDesc('id')->paginate(per_page($request));

            return PageResource::collection($items);
        }

        return view('admin.page.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.page.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
        Page::create($validated);

        return $this->success(trans('system.create_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        return view('admin.page.edit', [
            'item' => $page,
            'update_url' => route('admin.pages.update', $page->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
        $page->update($validated);

        return $this->success(trans('system.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return $this->success(trans('system.delete_success'));
    }
}
