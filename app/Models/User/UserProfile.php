<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\User;

use App\Enum\Gender;
use App\Models\Model;
use App\Models\System\Area;
use App\Models\User;
use App\Observers\UserProfileObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 用户资料
 *
 * @property int $user_id 用户ID
 * @property int $gender 性别：0保密/1男/2女
 * @property Carbon $birthday 生日
 * @property int $company_id 公司ID
 * @property int $province_id 省 ID
 * @property int $province_name 省
 * @property int $city_id 市 ID
 * @property int $city_name 市
 * @property int $district_id 区县ID
 * @property int $district_name 区县
 * @property string $website 个人网站
 * @property string $intro 个人介绍
 * @property string $bio 个性签名
 *
 * 只读属性
 * @property-read string|null $gender_label 性别标签
 *
 * 关系对象
 * @property User $user 用户实例
 * @property Area|null $province 省实例
 * @property Area|null $city 市实例
 * @property Area|null $district 区县实例
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[ObservedBy([UserProfileObserver::class])]
class UserProfile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gender', 'birthday', 'province_id',  'city_id', 'district_id', 'website', 'intro', 'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'user_id',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'gender' => Gender::GENDER_UNKNOWN->value,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'gender_label',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'gender' => Gender::class,
            'birthday' => 'date:Y-m-d',
            'province_id' => 'integer',
            'city_id' => 'integer',
            'district_id' => 'integer',
            'website' => 'string',
            'intro' => 'string',
            'bio' => 'string',
        ];
    }

    /**
     * Get the user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the province relation.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'province_id');
    }

    /**
     * Get the city relation.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'city_id');
    }

    /**
     * Get the district relation.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'district_id');
    }

    /**
     * 性别
     */
    protected function genderLabel(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, $attributes) => $this->gender->label()
        )->shouldCache();
    }
}
