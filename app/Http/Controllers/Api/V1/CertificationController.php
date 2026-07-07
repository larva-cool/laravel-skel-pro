<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enum\CertificationStatus;
use App\Enum\CertificationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubmitEnterpriseCertificationRequest;
use App\Http\Requests\Api\V1\SubmitPersonalCertificationRequest;
use App\Http\Resources\Api\V1\CertificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 实名认证接口
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class CertificationController extends Controller
{
    /**
     * CertificationController Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * 获取认证状态
     */
    public function show(Request $request): CertificationResource
    {
        $certification = $request->user()->certification;

        return new CertificationResource($certification);
    }

    /**
     * 提交个人实名认证
     */
    public function submitPersonal(SubmitPersonalCertificationRequest $request): JsonResponse
    {
        $user = $request->user();

        // 检查是否已认证
        if ($user->is_certified) {
            return response()->json(['message' => '您已完成实名认证，无需重复提交'], 400);
        }

        // 检查是否有待审核的认证
        if ($user->certification && $user->certification->is_pending) {
            return response()->json(['message' => '您的认证正在审核中，请耐心等待'], 400);
        }

        // 创建或更新认证信息
        $certification = $user->certification()->updateOrCreate(
            ['certifiable_type' => $user->getMorphClass(), 'certifiable_id' => $user->id],
            [
                'type' => CertificationType::PERSONAL,
                'real_name' => $request->real_name,
                'id_card_no' => $request->id_card_no,
                'id_card_front' => $request->id_card_front,
                'id_card_back' => $request->id_card_back,
                'id_card_in_hand' => $request->id_card_in_hand,
                'status' => CertificationStatus::PENDING,
                'failed_reason' => null,
                'submitted_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 标记为待审核
        $certification->markPending();

        return response()->json([
            'message' => '实名认证信息提交成功，请等待审核',
            'data' => new CertificationResource($certification),
        ]);
    }

    /**
     * 提交企业实名认证
     */
    public function submitEnterprise(SubmitEnterpriseCertificationRequest $request): JsonResponse
    {
        $user = $request->user();

        // 检查是否已认证
        if ($user->is_certified) {
            return response()->json(['message' => '您已完成实名认证，无需重复提交'], 400);
        }

        // 检查是否有待审核的认证
        if ($user->certification && $user->certification->is_pending) {
            return response()->json(['message' => '您的认证正在审核中，请耐心等待'], 400);
        }

        // 创建或更新认证信息
        $certification = $user->certification()->updateOrCreate(
            ['certifiable_type' => $user->getMorphClass(), 'certifiable_id' => $user->id],
            [
                'type' => CertificationType::ENTERPRISE,
                'real_name' => $request->enterprise_name,
                'id_card_no' => $request->license_no,
                'license' => $request->license,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'status' => CertificationStatus::PENDING,
                'failed_reason' => null,
                'submitted_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 标记为待审核
        $certification->markPending();

        return response()->json([
            'message' => '企业认证信息提交成功，请等待审核',
            'data' => new CertificationResource($certification),
        ]);
    }
}
