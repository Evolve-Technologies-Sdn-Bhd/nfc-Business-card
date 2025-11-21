# ✅ 批量应用设计功能

## 📝 功能概述

Business Plan 用户现在可以在 BusinessProfileBuilder 中将当前卡片的设计设置批量应用到其他卡片。

## 🎨 功能位置

**页面**: `/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder`
**位置**: 顶部 Header - 在卡片选择器和 Save 按钮之间
**按钮**: "Apply Design" (带画笔图标 🎨)

## 🎯 功能说明

### 1. **设计设置包含**

批量应用时会复制以下设计设置：
- ✅ **字体 (Font)**: Inter, Poppins, Roboto, Playfair
- ✅ **背景颜色 (Background Color)**: 自定义颜色
- ✅ **文字颜色 (Text Color)**: 自定义颜色
- ✅ **按钮样式 (Button Style)**: Solid, Outline, Soft, Shadow
- ✅ **主题 (Theme)**: Minimal, Modern, Creative, Professional, Dark
- ✅ **Profile Style**: Classic

### 2. **保留的内容**

以下内容**不会**被复制：
- ❌ 个人信息（姓名、职位、简历等）
- ❌ 公司信息
- ❌ 联系方式
- ❌ 链接
- ❌ 图片
- ❌ 统计数据
- ❌ 服务列表
- ❌ 团队成员

只复制**设计和样式**，内容保持不变。

## 🚀 使用流程

### 步骤 1: 选择源卡片（Admin 自己的卡片）
1. 在顶部点击卡片选择器
2. **只显示 Admin 自己的卡片**
   - 过滤条件：`card.user_id === currentUserId`
   - **不显示员工的卡片**（员工卡片的 `user_id` 是员工的 ID）
3. 选择一张已经设置好设计的卡片

### 步骤 2: 配置设计
1. 在各个 Tab 中调整字体、颜色、按钮样式等
2. 预览右侧的效果

### 步骤 3: 打开批量应用
1. 点击顶部的 **"Apply Design"** 按钮（在 Save 按钮旁边）
2. 打开批量应用设计的模态框

### 步骤 4: 查看当前设计
在模态框顶部可以看到当前设计预览：
- Font（字体）
- Theme（主题）
- Button Style（按钮样式）
- Colors（颜色预览方块）

### 步骤 5: 选择目标卡片（包括所有员工卡片）
1. 点击 **"Select All Employee Cards"** 一键选择所有员工卡片
   - 或者单独勾选特定卡片
2. 卡片列表显示：
   - Admin 自己的其他卡片
   - **所有员工的卡片**（带 "Employee" 紫色标签）
   - 显示格式：`{nfc_card_id} - {card_owner} - {status}`
   - 当前正在编辑的卡片不会显示
3. 右上角显示：`X / Y selected`（已选择 / 总数）

### 步骤 6: 批量应用
1. 点击 "Apply to X Card(s)" 按钮
2. 等待应用完成（显示 loading 动画）
3. 查看成功提示
4. 模态框自动关闭
5. 设计已应用到所选的所有卡片（包括员工卡片）

## 💻 技术实现

### 前端

#### 响应式数据
```javascript
const selectedCardsForDesign = ref([]); // 选中的卡片 IDs
const applyingDesign = ref(false);       // 应用中状态
const showApplyDesignModal = ref(false); // 模态框显示状态
const allEmployeeCards = ref([]);        // 所有员工的卡片
```

#### Computed Properties
```javascript
// 所有可用卡片（Admin 的其他卡片 + 所有员工卡片）
const allAvailableCards = computed(() => {
  const adminCards = userNfcCards.value.filter(c => c.id !== selectedNfcCardId.value);
  const employeeCards = allEmployeeCards.value;
  return [...adminCards, ...employeeCards];
});

// 检查是否全部选中
const isAllCardsSelected = computed(() => {
  return allAvailableCards.value.length > 0 && 
         selectedCardsForDesign.value.length === allAvailableCards.value.length;
});
```

#### 方法

```javascript
// 加载用户的卡片（只加载 Admin 自己的）
const loadUserNfcCards = async () => {
  const response = await $api.get("/nfc-cards");
  const currentUserId = authStore.user?.id;
  // 过滤：只显示 user_id 等于当前用户的卡片（Admin 自己的）
  // 员工卡片的 user_id 是员工的 ID，所以会被排除
  userNfcCards.value = response.nfc_cards.filter(card => {
    return card.user_id === currentUserId;
  });
};

// 加载所有员工的卡片
const loadAllEmployeeCards = async () => {
  // 使用同样的 API，但过滤出员工的卡片
  const response = await $api.get("/nfc-cards");
  const currentUserId = authStore.user?.id;
  // 过滤：user_id 不等于当前用户的卡片（员工的卡片）
  const employeeCardsList = response.nfc_cards.filter(card => {
    return card.user_id !== currentUserId;
  });
  allEmployeeCards.value = employeeCardsList.map(card => ({
    ...card,
    is_employee: true
  }));
};

// 切换全选/取消全选
const toggleSelectAllCards = () => {
  if (isAllCardsSelected.value) {
    selectedCardsForDesign.value = [];
  } else {
    selectedCardsForDesign.value = allAvailableCards.value.map(card => card.id);
  }
};

// 批量应用设计
const applyDesignToCards = async () => {
  // 1. 验证选择
  // 2. 提取当前设计设置
  // 3. 批量发送 API 请求（Promise.all）
  // 4. 显示结果并关闭模态框
};
```

#### API 调用

1. **获取所有卡片（Admin + 员工）**
```javascript
GET /nfc-cards

Response: {
  success: true,
  nfc_cards: [
    {
      id: 123,
      nfc_card_id: "NFC-XXX",
      card_owner: "Card Owner Name",
      status: "active",
      user_id: 456, // Card 拥有者的 user ID
      business_account_id: 789 // 如果是员工卡，指向 Admin 的 ID
    },
    // ...
  ]
}

// 前端通过 user_id 过滤：
// - Admin 的卡片：user_id === currentUserId
// - 员工的卡片：user_id !== currentUserId
```

2. **应用设计到卡片**
```javascript
POST /nfc-cards/{cardId}/apply-design

Body: {
  backgroundColor: "#FFFFFF",
  textColor: "#000000",
  font: "inter",
  buttonStyle: "solid",
  profileStyle: "classic",
  theme: "minimal"
}

Response: {
  success: true,
  message: "Design applied successfully"
}
```

### 后端需要实现

#### 新端点（只需要这一个）
```php
// app/Http/Controllers/Api/NfcCardController.php

/**
 * Apply design settings from one card to another
 * 
 * @param Request $request
 * @param int $id - Target card ID
 * @return JsonResponse
 */
public function applyDesign(Request $request, $id)
{
    $card = NfcCard::findOrFail($id);
    
    // Verify ownership
    if ($card->user_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    // Get design settings
    $designSettings = $request->validate([
        'backgroundColor' => 'string',
        'textColor' => 'string',
        'font' => 'string',
        'buttonStyle' => 'string',
        'profileStyle' => 'string',
        'theme' => 'string',
    ]);
    
    // Get or create landing page for this card
    $landingPage = LandingPage::firstOrCreate(
        ['nfc_card_id' => $card->id],
        ['user_id' => auth()->id()]
    );
    
    // Update only design fields
    $landingPage->update([
        'background_color' => $designSettings['backgroundColor'] ?? $landingPage->background_color,
        'text_color' => $designSettings['textColor'] ?? $landingPage->text_color,
        'font' => $designSettings['font'] ?? $landingPage->font,
        'button_style' => $designSettings['buttonStyle'] ?? $landingPage->button_style,
        'profile_style' => $designSettings['profileStyle'] ?? $landingPage->profile_style,
        'theme' => $designSettings['theme'] ?? $landingPage->theme,
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Design applied successfully'
    ]);
}
```

#### 路由
```php
// routes/api.php

Route::middleware('auth:sanctum')->prefix('nfc-cards')->group(function () {
    Route::post('/{id}/apply-design', [NfcCardController::class, 'applyDesign']);
});
```

## 🎨 UI 设计

### 顶部按钮
```vue
<!-- Apply Design Button -->
<button @click="showApplyDesignModal = true">
  <Icon name="heroicons:paint-brush" />
  <span>Apply Design</span>
</button>
```
- **位置**: Header 右侧，卡片选择器和 Save 按钮之间
- **图标**: 画笔图标 🎨
- **禁用条件**: 没有选中卡片或只有一张卡片

### 模态框设计

#### 1. 标题区域
- **标题**: "Apply Design to Cards"
- **关闭按钮**: X 图标

#### 2. 当前设计预览（蓝色背景）
- Font: 显示字体名称
- Theme: 显示主题名称
- Button Style: 显示按钮样式
- Colors: 两个颜色方块（背景色和文字色）

#### 3. 卡片选择列表
```vue
<label class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer border-2">
  <input type="checkbox" v-model="selectedCardsForDesign" :value="card.id" />
  <div class="ml-4 flex-1">
    <p class="text-sm font-semibold">{{ card.nfc_card_id }}</p>
    <p class="text-xs text-gray-600">{{ card.card_owner }}</p>
  </div>
  <span class="px-2 py-1 text-xs font-medium rounded-full">{{ card.status }}</span>
</label>
```
- **高度**: max-h-64，支持滚动
- **选中状态**: 蓝色边框和浅蓝背景
- **空状态**: 显示"No other cards available"

#### 4. 操作按钮
- **Cancel**: 灰色按钮，关闭模态框
- **Apply**: 
  - 默认: "Select Cards"
  - 已选择: "Apply to X Card(s)"
  - 应用中: "Applying..." + Spinner
  - 禁用: 没有选中卡片时

### 成功/错误提示
- ✅ "Design applied to 3 cards successfully!" + 自动关闭模态框
- ❌ "Failed to apply design to 1 card"

## 📋 验证流程

### 前端验证
1. 至少选择一张目标卡片
2. 必须有源卡片被选中
3. 不能应用到当前卡片

### 卡片分离逻辑
1. **顶部卡片选择器（userNfcCards）**
   - 过滤条件：`card.user_id === currentUserId`
   - 只显示 `user_id` 等于当前登录用户的卡片
   - 这些是 Admin 自己的卡片
   - 员工卡片的 `user_id` 是员工的 ID，所以不显示
   - 用于选择源卡片（复制设计的来源）

2. **Apply Design 模态框（allAvailableCards）**
   - 显示：Admin 的其他卡片（除了当前选中的）+ 所有员工卡片
   - 员工卡片从 `/business/all-cards` 加载
   - 带 "Employee" 紫色标签标识
   - 支持一键全选所有可用卡片

### 后端验证
1. 验证用户拥有目标卡片
2. 验证设计参数格式
3. 安全更新，只修改设计字段

## 🔐 权限控制

- ✅ 只有 Business Plan 用户可见
- ✅ 只能应用到自己的卡片
- ✅ Employee 卡片也支持（如果是 Business Admin）

## ✨ 用户体验

### 优点
1. **快速统一**: 一次性更新多张卡片的设计
2. **保留内容**: 不影响卡片的个人信息
3. **实时反馈**: 显示成功/失败的卡片数量
4. **清晰提示**: 明确说明哪些内容会被复制

### 使用场景
1. **品牌统一**: 公司所有员工卡片使用统一设计
2. **主题切换**: 快速更换所有卡片的配色
3. **批量更新**: 同时更新多张卡片的字体或按钮样式

## 🧪 测试检查清单

- [ ] 选择卡片后应用按钮文字更新
- [ ] 应用过程中显示 loading 状态
- [ ] 成功后清除选择列表
- [ ] 显示正确的成功/失败数量
- [ ] 目标卡片的内容保持不变
- [ ] 目标卡片的设计正确更新
- [ ] 当前卡片不显示在选择列表中
- [ ] 无卡片时按钮禁用
- [ ] 错误处理正确显示

## 📊 API 响应示例

### 成功
```json
{
  "success": true,
  "message": "Design applied successfully"
}
```

### 失败
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

## 🎉 功能已实现

前端功能已完全实现：
- ✅ UI 界面
- ✅ 卡片选择逻辑（复用现有 `/nfc-cards` API）
- ✅ 批量应用方法
- ✅ 加载状态
- ✅ 成功/错误提示
- ✅ Console.log 调试信息

### 📋 **后端需求**

**只需要实现一个新端点：**
```
POST /nfc-cards/{id}/apply-design
```

**不需要新增的端点：**
- ❌ ~~/business/all-cards~~ （使用现有的 `/nfc-cards` 通过前端过滤即可）

### 🔍 **调试信息**

打开浏览器控制台可以看到：
- "Loading employee cards..." - 开始加载员工卡片
- "All cards response for employee filtering:" - API 响应
- "Employee cards loaded: X" - 加载了多少张员工卡片
- "allAvailableCards computed: {...}" - 可用卡片统计
