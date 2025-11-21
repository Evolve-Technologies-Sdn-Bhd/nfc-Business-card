# 需要的后端 API 端点

## 问题
Settings.vue 需要获取计划价格和功能数据，但目前只有管理员端点 `/admin/plan-prices` 可用，普通用户无法访问。

## 解决方案
创建一个公开的 API 端点，直接从 `plan_prices` 数据库表读取数据，让所有认证用户都能获取活跃的计划信息。

## 建议的后端实现

### 1. 创建公开的计划端点

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/plans', [PlanController::class, 'getPublicPlans']);
    // 或者
    Route::get('/subscription/plans', [PlanController::class, 'getPublicPlans']);
});
```

### 2. Controller 方法

```php
// app/Http/Controllers/Api/PlanController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Get public plan information for all authenticated users
     * Directly reads from plan_prices database table
     */
    public function getPublicPlans()
    {
        try {
            // 直接从 plan_prices 表读取活跃的计划
            $plans = DB::table('plan_prices')
                ->where('is_active', true)
                ->select([
                    'id',
                    'plan_type',
                    'price',
                    'currency',
                    'description',
                    'features',
                    'is_active',
                    'updated_at'
                ])
                ->orderBy('price', 'asc')
                ->get()
                ->map(function ($plan) {
                    // 解码 JSON features 字段
                    $plan->features = json_decode($plan->features, true) ?? [];
                    return $plan;
                });

            return response()->json([
                'success' => true,
                'data' => $plans
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load plans'
            ], 500);
        }
    }
}
```

### 3. 数据结构
端点应该返回与 `/admin/plan-prices` 相同的数据结构：

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "plan_type": "free",
      "price": 0,
      "currency": "MYR",
      "description": "Basic features for personal use",
      "features": [
        "Basic profile creation",
        "Limited NFC cards",
        "Basic analytics",
        "Community support"
      ],
      "is_active": true,
      "updated_at": "2025-11-20T08:00:00.000000Z"
    },
    {
      "id": 2,
      "plan_type": "business", 
      "price": 9.99,
      "currency": "MYR",
      "description": "Advanced features for business professionals",
      "features": [
        "Unlimited NFC cards",
        "Advanced analytics",
        "Custom branding",
        "Priority support",
        "Team management",
        "Bulk operations"
      ],
      "is_active": true,
      "updated_at": "2025-11-20T08:00:00.000000Z"
    }
  ]
}
```

## 优势

1. **数据一致性**: 与 price-management.vue 使用相同的数据源
2. **实时更新**: 管理员在 price-management 中的更改立即反映在用户界面
3. **权限安全**: 只暴露必要的公开信息
4. **性能优化**: 只查询活跃计划，减少数据传输

## 前端使用

前端 Settings.vue 已经配置为尝试多个端点：
- `/plans`
- `/plan-prices` 
- `/subscription/plans`
- `/admin/plan-prices` (仅管理员)

选择其中任何一个端点名称实现即可。

## 测试

实现后，可以通过以下方式测试：
```bash
# 使用认证用户的 token
curl -H "Authorization: Bearer {token}" \
     -H "Accept: application/json" \
     http://your-domain/api/plans
```

应该返回活跃计划的完整信息。
