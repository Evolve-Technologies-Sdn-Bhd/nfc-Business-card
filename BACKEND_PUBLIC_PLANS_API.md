# 需要创建公开的计划价格 API

## 问题
Settings.vue 需要获取 price-management 的实时数据，但 `/admin/plan-prices` 端点只有管理员能访问。

## 解决方案
创建一个公开的端点，让所有认证用户都能获取计划价格信息。

## 后端实现

### 1. 创建公开的计划控制器

```php
// app/Http/Controllers/Api/PublicPlanController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PublicPlanController extends Controller
{
    /**
     * Get public plan prices for all authenticated users
     * This syncs with price-management data
     */
    public function getPlans()
    {
        try {
            // 直接从 plan_prices 表读取活跃计划
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

### 2. 添加路由

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // 公开的计划价格端点
    Route::get('/plans', [PublicPlanController::class, 'getPlans']);
});
```

### 3. 数据同步机制

当管理员在 price-management 中更新价格时：
1. 数据保存到 `plan_prices` 表
2. 公开端点 `/api/plans` 自动返回最新数据
3. Settings.vue 获取到实时更新的价格

## 前端使用

Settings.vue 中的 `loadPlanPrices` 函数会：

```javascript
// 1. 首先尝试公开端点
const response = await $api.get("/plans");

// 2. 如果失败，尝试管理员端点（如果用户是管理员）
if (!response.success && authStore.user?.admin_role) {
    const adminResponse = await $api.get("/admin/plan-prices");
}

// 3. 最后使用 fallback 数据
```

## 优势

1. **实时同步**: price-management 的更改立即反映在 Settings 页面
2. **权限安全**: 普通用户可以访问，但只能看到公开信息
3. **数据一致**: 直接从同一个数据库表读取
4. **性能优化**: 避免权限错误和多次 API 调用

## 测试

```bash
# 测试公开端点
curl -H "Authorization: Bearer {user_token}" \
     -H "Accept: application/json" \
     http://localhost:8000/api/plans

# 应该返回
{
  "success": true,
  "data": [
    {
      "id": 1,
      "plan_type": "basic",
      "price": 99.00,
      "currency": "MYR",
      "description": "Basic NFC Card Plan",
      "features": ["One NFC card", "Basic profile", ...],
      "is_active": true
    }
  ]
}
```

## 实现后的效果

1. **管理员更新价格** → price-management.vue 保存到数据库
2. **用户访问 Settings** → 自动获取最新价格
3. **实时同步** → 无需重启应用或清除缓存
4. **无权限错误** → 所有用户都能正常访问
