<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DateTimeFormatter;

/**
 * 模型基类
 *
 * @method [返回值类型] [方法名]([参数列表]) [可选描述]
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    use DateTimeFormatter;
}
