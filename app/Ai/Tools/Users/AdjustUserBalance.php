<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */
declare(strict_types=1);

namespace App\Ai\Tools\Users;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * 前台用户余额调整工具（积分 / 金币）。
 *
 * 支持增加或扣减用户的积分、金币；写操作，默认需要二次确认。
 */
class AdjustUserBalance implements Approvable, Tool
{
    use InteractsWithApprovals;

    /**
     * 工具描述。
     */
    public function description(): Stringable|string
    {
        return '调整前台用户的积分或金币余额。支持「增加」或「扣减」两种操作，可同时调整积分和金币。该操作为敏感写操作，执行前会展示变更摘要并请求二次确认。';
    }

    /**
     * 参数 Schema。
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->required()
                ->description('目标用户 ID'),
            'currency' => $schema->string()
                ->enum(['points', 'coins'])
                ->required()
                ->description('调整的货币类型：points=积分，coins=金币'),
            'operation' => $schema->string()
                ->enum(['add', 'subtract'])
                ->required()
                ->description('操作类型：add=增加，subtract=扣减'),
            'amount' => $schema->integer()
                ->required()
                ->min(1)
                ->description('变动数量，必须为正整数，具体增减由 operation 决定'),
            'reason' => $schema->string()
                ->max(200)
                ->description('调整原因，将展示在二次确认信息中'),
        ];
    }

    /**
     * 二次确认信息。
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        $user = User::query()->find($request->integer('id'));
        if (! $user) {
            return false;
        }

        $currency = $request->string('currency')->toString() === 'coins' ? '金币' : '积分';
        $operation = $request->string('operation')->toString() === 'add' ? '增加' : '扣减';
        $amount = $request->integer('amount');
        $current = $request->string('currency')->toString() === 'coins'
            ? $user->available_coins
            : $user->available_points;
        $reason = $request->string('reason')->toString();

        $message = "即将为用户 [{$user->id}] {$user->name}（{$user->username}）{$operation} {$amount} {$currency}。当前余额：{$current}，变更后预计：".($operation === '增加' ? $current + $amount : max(0, $current - $amount)).'。';
        if ($reason !== '') {
            $message .= " 原因：{$reason}。";
        }

        return Approval::required($message);
    }

    /**
     * 执行调整。
     */
    public function handle(Request $request): Stringable|string
    {
        $user = User::query()->find($request->integer('id'));
        if (! $user) {
            return '未找到目标用户，操作已取消。';
        }

        $field = $request->string('currency')->toString() === 'coins'
            ? 'available_coins'
            : 'available_points';
        $amount = $request->integer('amount');
        $isAdd = $request->string('operation')->toString() === 'add';

        $before = (int) $user->{$field};
        $after = $isAdd ? $before + $amount : max(0, $before - $amount);

        $user->forceFill([$field => $after])->save();

        $currencyName = $field === 'available_coins' ? '金币' : '积分';
        $operationName = $isAdd ? '增加' : '扣减';

        return "已为用户 [{$user->id}] {$user->name} {$operationName} {$amount} {$currencyName}。变动前：{$before}，变动后：{$after}。";
    }
}
