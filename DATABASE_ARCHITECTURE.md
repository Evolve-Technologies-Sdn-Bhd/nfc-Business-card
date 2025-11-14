# 🗄️ NFC Business Card - Database Architecture

## 📋 目录

1. [数据库配置](#数据库配置)
2. [数据流向](#数据流向)
3. [核心数据表](#核心数据表)
4. [数据关系图](#数据关系图)
5. [API 端点映射](#api-端点映射)
6. [数据修改位置](#数据修改位置)

---

## 🔧 数据库配置

### 主要配置文件

```
📁 backend/
  ├── .env                           # 环境变量（数据库连接信息）
  ├── config/database.php            # 数据库配置
  └── database/
      ├── migrations/                # 数据库表结构定义
      └── seeders/                   # 测试数据
```

### 当前数据库设置

**文件**: `backend/.env`

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nfc_business_card
DB_USERNAME=root
DB_PASSWORD=
```

**数据库**: `nfc_business_card` (MySQL)
**位置**: 本地 MySQL 服务器 (127.0.0.1:3306)

---

## 🔄 数据流向

### 1️⃣ 用户注册 → 数据保存流程

```
Frontend (注册表单)
    ↓
    POST /api/register
    ↓
Backend: AuthController@register
    ↓
    保存到 users 表
    ↓
    自动触发: User::boot() → created event
    ↓
    自动创建 profiles 表记录
    ↓
    返回 JWT Token
    ↓
Frontend: 保存到 localStorage
```

### 2️⃣ NFC 卡片订购 → 数据保存流程

```
Frontend: CardManagement.vue
    ↓
    用户填写订单表单
    ↓
    POST /api/nfc-cards
    ↓
Backend: NfcCardController@store
    ↓
    验证 Premium 订阅
    ↓
    保存到 nfc_cards 表
    ├── 自动生成 card_id
    ├── 自动生成 nfc_card_id
    └── 保存订单信息
    ↓
    更新 users 表的订阅信息
    ├── subscription_plan
    ├── subscription_start_date
    └── subscription_end_date
    ↓
    创建通知记录到 notifications 表
    ↓
    返回卡片信息
    ↓
Frontend: 重定向到设计页面
```

### 3️⃣ Profile Builder → Landing Page 保存

```
Frontend: ProfileBuilder.vue / BusinessProfileBuilder.vue
    ↓
    用户编辑个人资料
    ↓
    PUT /api/nfc-cards/{id}/landing-page
    ↓
Backend: NfcCardController@updateLandingPage
    ↓
    查找或创建 landing_pages 表记录
    ├── nfc_card_id (外键)
    ├── 基本信息 (name, title, bio...)
    ├── 公司信息 (company_name, logo...)
    ├── 社交链接 (social_links JSON)
    ├── 设计设置 (theme, colors, fonts...)
    └── 图片路径 (profile_image, company_logo)
    ↓
    保存到数据库
    ↓
    返回成功消息
    ↓
Frontend: 显示保存成功 Toast
```

### 4️⃣ 图片上传 → 存储流程

```
Frontend: ProfileImageUpload.vue
    ↓
    选择图片文件
    ↓
    POST /api/upload-image
    ↓
Backend: ProfileController@uploadImage
    ↓
    验证文件类型和大小
    ↓
    保存到: storage/app/public/
    ├── profile-images/
    └── company-logos/
    ↓
    返回相对路径: /storage/profile-images/xxx.jpg
    ↓
Frontend: 拼接完整 URL
    http://localhost:8000/storage/profile-images/xxx.jpg
    ↓
    保存路径到 landing_pages 表
```

---

## 📊 核心数据表

### 1. `users` 表 - 用户账户

**文件**: `backend/database/migrations/2025_07_28_052221_create_users_table.php`

**重要约束**:

- ✅ `email` = **UNIQUE** - 用于登录认证，必须唯一
- ✅ 每个用户自动创建一个 `profiles` 记录 (1:1)

**字段**:
| 字段 | 类型 | 说明 | 约束 | 可修改位置 |
|------|------|------|------|-----------|
| id | bigint | 主键 | PRIMARY KEY | 自动生成 |
| first_name | string | 名字 | - | Settings.vue → PUT /api/user/account |
| last_name | string | 姓氏 | - | Settings.vue → PUT /api/user/account |
| email | string | 邮箱 | **UNIQUE** 🔑 | Settings.vue → PUT /api/user/account |
| password | string | 密码 (加密) | - | 密码重置流程 |
| phone | string | 电话 | - | Settings.vue → PUT /api/user/account |
| company | string | 公司名称 | - | Settings.vue → PUT /api/user/account |
| job_title | string | 职位 | - | Settings.vue → PUT /api/user/account |
| plan | enum | 订阅计划 | - | CardManagement → POST /api/nfc-cards |
| subscription_plan | enum | 当前计划 | - | CardManagement → POST /api/nfc-cards |
| subscription_start_date | date | 订阅开始日期 | - | 自动设置 |
| subscription_end_date | date | 订阅结束日期 | - | 自动设置 |
| subscription_active | boolean | 订阅状态 | - | 自动设置 |
| has_physical_card | boolean | 是否有实体卡 | - | 自动设置 |
| is_admin | boolean | 管理员权限 | - | Admin Panel |
| parent_business_id | bigint | 企业主账户 ID | FOREIGN KEY | 企业账户管理 |

**为什么 email 必须 UNIQUE？**

- 🔐 用户使用 email 登录系统
- 🔐 密码重置功能依赖 email
- 🔐 OAuth 登录（Google, Apple）使用 email 识别用户
- 🔐 防止重复注册

**修改位置**:

- **Frontend**: `pages/UserDashboard/Settings.vue`
- **API**: `PUT /api/user/account`
- **Controller**: `ProfileController@updateUserInfo`

---

### 2. `nfc_cards` 表 - NFC 实体卡

**文件**: `backend/database/migrations/2025_07_30_180000_create_nfc_cards_table.php`

**字段**:
| 字段 | 类型 | 说明 | 可修改位置 |
|------|------|------|-----------|
| id | bigint | 主键 | 自动生成 |
| user_id | bigint | 用户 ID (外键) | 关联 users 表 |
| card_id | string | 卡片 ID (唯一) | 自动生成: CARD-XXXXXXXX |
| nfc_card_id | string | NFC 芯片 ID (唯一) | 自动生成: NFC-XXXXXXXXXXXX |
| card_owner | string | 卡片所有者 | CardManagement → Order Modal |
| billing_address | text | 账单地址 | CardManagement → Order Modal |
| contact_number | string | 联系电话 | CardManagement → Order Modal |
| purchase_date | date | 购买日期 | 自动设置为 now() |
| expiry_date | date | 到期日期 | 自动计算 (1 年后) |
| status | enum | 状态 | activate/deactivate 功能 |
| subscription_plan | enum | 订阅计划 | CardManagement → Order Modal |
| purchase_amount | decimal | 购买金额 | 根据计划自动设置 |
| shipping_address | text | 配送地址 | CardManagement → Order Modal |
| notes | text | 备注 | CardManagement → Order Modal |

**状态值**:

- `active` - 激活
- `inactive` - 未激活
- `expired` - 已过期
- `replacement` - 替换卡

**修改位置**:

- **Frontend**: `pages/UserDashboard/CardManagement.vue`
- **Frontend**: `pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement.vue`
- **API**:
  - `POST /api/nfc-cards` - 创建订单
  - `PUT /api/nfc-cards/{id}` - 更新信息
  - `POST /api/nfc-cards/{id}/activate` - 激活
  - `POST /api/nfc-cards/{id}/deactivate` - 停用
- **Controller**: `NfcCardController`

---

### 3. `nfc_tags` 表 - NFC 标签/虚拟卡

**文件**: `backend/database/migrations/2025_07_28_052223_create_nfc_tags_table.php`

**字段**:
| 字段 | 类型 | 说明 | 可修改位置 |
|------|------|------|-----------|
| id | bigint | 主键 | 自动生成 |
| user_id | bigint | 用户 ID | 关联 users 表 |
| nfc_id | string | NFC ID (唯一) | 用户输入或生成 |
| name | string | 标签名称 | ProfileBuilder |
| status | enum | 状态 | activate/deactivate |
| tap_count | integer | 点击次数 | 自动增加 |
| last_tapped_at | timestamp | 最后点击时间 | 自动更新 |
| nfc_card_id | string | 关联实体卡 | 外键到 nfc_cards |

**修改位置**:

- **Frontend**: NFC 激活流程
- **API**: `POST /api/nfc/activate`
- **Controller**: `NfcController@activate`

---

### 4. `landing_pages` 表 - 个人资料页面

**文件**: `backend/database/migrations/2025_11_13_072228_create_landing_pages_table.php`

**字段分组**:

#### 基本信息

| 字段          | 类型   | 说明               |
| ------------- | ------ | ------------------ |
| nfc_card_id   | bigint | NFC 卡片 ID (外键) |
| name          | string | 姓名               |
| title         | string | 职位               |
| qualification | string | 资格认证           |
| bio           | text   | 个人简介           |
| phone         | string | 电话               |
| email         | string | 邮箱               |
| website       | string | 网站               |
| address       | text   | 地址               |
| profile_image | string | 头像路径           |
| company_logo  | string | 公司 Logo 路径     |

#### 公司信息

| 字段                    | 类型   | 说明      |
| ----------------------- | ------ | --------- |
| company_logo_text       | string | Logo 文字 |
| company_name            | string | 公司名称  |
| company_registration_no | string | 注册号    |
| company_department      | string | 部门      |

#### 地址详情

| 字段               | 类型   | 说明     |
| ------------------ | ------ | -------- |
| address_name       | string | 地址名称 |
| address_street     | string | 街道     |
| address_area       | string | 区域     |
| address_city_state | string | 城市/州  |
| address_country    | string | 国家     |
| address_map_url    | string | 地图 URL |

#### JSON 字段

| 字段         | 类型 | 说明     | 示例                                    |
| ------------ | ---- | -------- | --------------------------------------- |
| stats        | JSON | 统计数据 | `[{"label":"Projects","value":"100+"}]` |
| services     | JSON | 服务列表 | `[{"title":"Web Design","icon":"..."}]` |
| social_links | JSON | 社交链接 | `[{"platform":"LinkedIn","url":"..."}]` |
| team_members | JSON | 团队成员 | `[{"name":"John","title":"CEO"}]`       |

#### 联系方式

| 字段            | 类型   | 说明          |
| --------------- | ------ | ------------- |
| phone_number    | string | 电话号码      |
| phone_label     | string | 电话标签      |
| email_address   | string | 邮箱地址      |
| email_label     | string | 邮箱标签      |
| whatsapp_number | string | WhatsApp 号码 |
| whatsapp_label  | string | WhatsApp 标签 |
| website_url     | string | 网站 URL      |
| website_label   | string | 网站标签      |

#### 设计设置

| 字段             | 类型    | 说明         | 可选值                          |
| ---------------- | ------- | ------------ | ------------------------------- |
| profile_style    | string  | 个人资料样式 | classic, modern                 |
| theme            | string  | 主题         | minimal, gradient, dark, light  |
| background_color | string  | 背景颜色     | HEX 颜色值                      |
| text_color       | string  | 文字颜色     | HEX 颜色值                      |
| font             | string  | 字体         | inter, roboto, montserrat, etc. |
| button_style     | string  | 按钮样式     | solid, outline, soft, shadow    |
| show_watermark   | boolean | 显示水印     | true/false                      |

**修改位置**:

- **Frontend**:
  - `pages/UserDashboard/ProfileBuilder.vue`
  - `pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder.vue`
- **API**: `PUT /api/nfc-cards/{id}/landing-page`
- **Controller**: `NfcCardController@updateLandingPage` (第 320-380 行)

---

### 5. `profiles` 表 - 用户个人资料

**文件**: `backend/database/migrations/2025_07_28_052223_create_profiles_table.php`

**关系**: 1 User = 1 Profile (一对一关系) ✅

**字段**:
| 字段 | 类型 | 说明 | 约束 |
|------|------|------|------|
| id | bigint | 主键 | PRIMARY KEY |
| user_id | bigint | 用户 ID (外键) | UNIQUE, FOREIGN KEY |
| slug | string | URL 友好标识符 | UNIQUE |
| name | string | 显示名称 | - |
| title | string | 职位 | - |
| company | string | 公司 | - |
| bio | text | 简介 | - |
| email | string | 邮箱 | - |
| phone | string | 电话 | - |
| location | string | 位置 | - |
| avatar | string | 头像路径 | - |
| background_image | string | 背景图路径 | - |

**重要**: `user_id` 设置了 UNIQUE 约束，确保一个用户只能有一个 profile

**修改位置**:

- **API**: `PUT /api/user/profile`
- **Controller**: `ProfileController@update`

---

### 6. `notifications` 表 - 系统通知

**文件**: `backend/database/migrations/2025_01_12_000001_create_notifications_table.php`

**通知类型**:

- `nfc_card_purchased` - NFC 卡购买
- `nfc_card_shipped` - 卡片发货
- `nfc_card_delivered` - 卡片送达
- `subscription_expiring` - 订阅即将到期
- `landing_page_viewed` - 个人页面被查看

---

## 🔗 数据关系图

### 正确的数据关系

```
                    ┌──────────────────────────┐
                    │        users             │
                    │ ─────────────────────    │
                    │  id (PK)                 │
                    │  email (UNIQUE)          │
                    │  subscription_plan       │
                    └──────────────────────────┘
                          │                │
                    1:1   │                │  1:N
                          │                │
                          ▼                ▼
              ┌──────────────────┐  ┌──────────────────┐
              │   profiles       │  │   nfc_cards      │
              │ ──────────────── │  │ ──────────────── │
              │  id (PK)         │  │  id (PK)         │
              │  user_id (UK,FK) │  │  user_id (FK)    │◄─┐
              │  slug (UNIQUE)   │  │  card_id (UK)    │  │
              │  name            │  │  nfc_card_id(UK) │  │
              │  title           │  │  status          │  │
              └──────────────────┘  └──────────────────┘  │
                                           │              │
                                      1:1  │              │
                                           │              │
                                           ▼              │
                                    ┌──────────────────┐  │
                                    │ landing_pages    │  │
                                    │ ──────────────── │  │
                                    │  id (PK)         │  │
                                    │  nfc_card_id(FK) │──┘
                                    │  name            │
                                    │  profile_image   │
                                    │  company_logo    │
                                    │  social_links    │
                                    │  theme, design   │
                                    └──────────────────┘
```

### 关系说明

1. **users → profiles** (1:1)

   - ✅ 一个用户账户只能有一个 profile
   - `profiles.user_id` 有 UNIQUE 约束
   - 用户注册时自动创建

2. **users → nfc_cards** (1:N)

   - ✅ 一个用户可以订购多张 NFC 卡
   - 每张卡独立管理
   - 可以是不同的订阅计划

3. **nfc_cards → landing_pages** (1:1)
   - ✅ 一张 NFC 卡只能有一个 Landing Page
   - 每张卡的内容独立
   - 在 ProfileBuilder 中编辑

---

## 🛠️ API 端点映射

### 用户管理

| 端点                | 方法 | 功能     | 修改的表        |
| ------------------- | ---- | -------- | --------------- |
| `/api/register`     | POST | 注册     | users, profiles |
| `/api/login`        | POST | 登录     | sessions        |
| `/api/user/account` | PUT  | 更新账户 | users           |
| `/api/user/profile` | PUT  | 更新资料 | profiles        |

### NFC 卡片管理

| 端点                               | 方法 | 功能              | 修改的表                        |
| ---------------------------------- | ---- | ----------------- | ------------------------------- |
| `/api/nfc-cards`                   | GET  | 获取卡片列表      | -                               |
| `/api/nfc-cards`                   | POST | 创建订单          | nfc_cards, users, notifications |
| `/api/nfc-cards/{id}`              | GET  | 获取卡片详情      | -                               |
| `/api/nfc-cards/{id}`              | PUT  | 更新卡片          | nfc_cards                       |
| `/api/nfc-cards/{id}/activate`     | POST | 激活卡片          | nfc_cards, nfc_tags             |
| `/api/nfc-cards/{id}/deactivate`   | POST | 停用卡片          | nfc_cards                       |
| `/api/nfc-cards/{id}/landing-page` | GET  | 获取 Landing Page | landing_pages                   |
| `/api/nfc-cards/{id}/landing-page` | PUT  | 更新 Landing Page | landing_pages                   |

### NFC 标签管理

| 端点                   | 方法 | 功能         | 修改的表            |
| ---------------------- | ---- | ------------ | ------------------- |
| `/api/nfc/activate`    | POST | 激活 NFC     | nfc_tags            |
| `/api/nfc/tags`        | GET  | 获取标签列表 | -                   |
| `/api/nfc/tap/{nfcId}` | POST | 记录点击     | nfc_tags, analytics |

### 文件上传

| 端点                | 方法 | 功能     | 存储位置              |
| ------------------- | ---- | -------- | --------------------- |
| `/api/upload-image` | POST | 上传图片 | `storage/app/public/` |

---

## 📂 数据修改位置完整列表

### Frontend 页面 → API → Database

#### 1. 用户设置

```
Frontend: pages/UserDashboard/Settings.vue
    ↓
API: PUT /api/user/account
    ↓
Controller: ProfileController@updateUserInfo
    ↓
Database: users 表
    - first_name
    - last_name
    - email
    - phone
    - company
    - job_title
```

#### 2. NFC 卡片订购

```
Frontend: pages/UserDashboard/CardManagement.vue
         pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement.vue
    ↓
API: POST /api/nfc-cards
    ↓
Controller: NfcCardController@store
    ↓
Database:
    - nfc_cards 表 (新记录)
    - users 表 (更新订阅信息)
    - notifications 表 (创建通知)
```

#### 3. Profile Builder (个人资料编辑)

```
Frontend: pages/UserDashboard/ProfileBuilder.vue
         pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder.vue
    ↓
API: PUT /api/nfc-cards/{id}/landing-page
    ↓
Controller: NfcCardController@updateLandingPage
    ↓
Database: landing_pages 表
    - 基本信息 (name, title, bio...)
    - 公司信息 (company_name, logo...)
    - 联系方式 (phone, email, whatsapp...)
    - 社交链接 (social_links JSON)
    - 设计设置 (theme, colors, fonts...)
    - 图片路径 (profile_image, company_logo)
```

#### 4. 图片上传

```
Frontend: components/ProfileImageUpload.vue
    ↓
API: POST /api/upload-image
    ↓
Controller: ProfileController@uploadImage
    ↓
File System: storage/app/public/
    - profile-images/
    - company-logos/
    ↓
Database: landing_pages 表
    - profile_image (保存相对路径)
    - company_logo (保存相对路径)
```

#### 5. 卡片激活/停用

```
Frontend: pages/UserDashboard/CardManagement.vue
    ↓ (激活)
API: POST /api/nfc-cards/{id}/activate
    ↓
Controller: NfcCardController@activate
    ↓
Database:
    - nfc_cards 表 (status = 'active')
    - nfc_tags 表 (创建新记录)

    ↓ (停用)
API: POST /api/nfc-cards/{id}/deactivate
    ↓
Controller: NfcCardController@deactivate
    ↓
Database:
    - nfc_cards 表 (status = 'inactive')
```

---

## 🔐 数据库访问权限

### 开发环境

- **Host**: 127.0.0.1
- **Port**: 3306
- **Database**: nfc_business_card
- **Username**: root
- **Password**: (空)

### 修改数据库连接

**文件**: `backend/.env`

```dotenv
DB_CONNECTION=mysql          # 数据库类型
DB_HOST=127.0.0.1           # 主机地址
DB_PORT=3306                # 端口
DB_DATABASE=nfc_business_card  # 数据库名
DB_USERNAME=root            # 用户名
DB_PASSWORD=                # 密码
```

---

## 🚀 数据库操作命令

### 运行迁移 (创建表)

```bash
cd backend
php artisan migrate
```

### 回滚迁移

```bash
php artisan migrate:rollback
```

### 重置数据库 (删除所有数据)

```bash
php artisan migrate:fresh
```

### 查看迁移状态

```bash
php artisan migrate:status
```

### 创建新迁移

```bash
php artisan make:migration create_table_name
```

---

## 📝 常见数据修改场景

### 场景 1: 修改用户订阅计划

**位置**:

1. Frontend: `CardManagement.vue` → Order Modal
2. API: `POST /api/nfc-cards`
3. 代码: `NfcCardController@store` (第 106-114 行)

**涉及表**:

- `users.subscription_plan`
- `users.subscription_start_date`
- `users.subscription_end_date`
- `nfc_cards.subscription_plan`

---

### 场景 2: 修改 Landing Page 设计

**位置**:

1. Frontend: `ProfileBuilder.vue` → Design Settings Tab
2. API: `PUT /api/nfc-cards/{id}/landing-page`
3. 代码: `NfcCardController@updateLandingPage` (第 320-380 行)

**涉及字段**:

- `landing_pages.theme`
- `landing_pages.background_color`
- `landing_pages.text_color`
- `landing_pages.font`
- `landing_pages.button_style`

---

### 场景 3: 上传个人头像

**位置**:

1. Frontend: `ProfileImageUpload.vue`
2. API: `POST /api/upload-image`
3. 存储: `storage/app/public/profile-images/`
4. 保存: `landing_pages.profile_image`

**完整路径**:

```
上传文件 → storage/app/public/profile-images/xxx.jpg
保存到数据库 → /storage/profile-images/xxx.jpg
前端访问 → http://localhost:8000/storage/profile-images/xxx.jpg
```

---

## 🔍 快速查找数据位置

| 想要修改...      | 前往文件                              | 数据库表                   |
| ---------------- | ------------------------------------- | -------------------------- |
| 用户基本信息     | Settings.vue                          | users                      |
| 订阅计划         | CardManagement.vue                    | users, nfc_cards           |
| 个人资料页面内容 | ProfileBuilder.vue                    | landing_pages              |
| 卡片状态         | CardManagement.vue                    | nfc_cards                  |
| 图片/Logo        | ProfileImageUpload.vue                | landing_pages, storage/    |
| 社交链接         | ProfileBuilder.vue (Social Links Tab) | landing_pages.social_links |
| 公司信息         | ProfileBuilder.vue (Company Info)     | landing_pages              |
| 设计主题         | ProfileBuilder.vue (Design Settings)  | landing_pages              |

---

## 📊 数据库 ER 图详细版

```
┌────────────────────────────────────────────┐
│                  users                     │
├────────────────────────────────────────────┤
│ PK: id                                     │
│ UK: email                                  │
│ - first_name, last_name                    │
│ - phone, company, job_title                │
│ - subscription_plan                        │
│ - subscription_start_date                  │
│ - subscription_end_date                    │
│ - subscription_active                      │
│ - has_physical_card                        │
│ - is_admin, parent_business_id             │
└────────────────────────────────────────────┘
         │                        │
         │ 1:1                    │ 1:N
         │ (UNIQUE)               │ (可以有多张卡)
         │                        │
         ▼                        ▼
┌──────────────────┐    ┌─────────────────────┐
│    profiles      │    │     nfc_cards       │
├──────────────────┤    ├─────────────────────┤
│ PK: id           │    │ PK: id              │
│ FK: user_id (UK) │    │ FK: user_id         │
│ UK: slug         │    │ UK: card_id         │
│ - name           │    │ UK: nfc_card_id     │
│ - title          │    │ - card_owner        │
│ - bio            │    │ - status            │
│ - avatar         │    │ - subscription_plan │
└──────────────────┘    │ - purchase_date     │
                        │ - expiry_date       │
                        └─────────────────────┘
                                  │
                                  │ 1:1
                                  │ (每张卡一个页面)
                                  │
                                  ▼
                        ┌─────────────────────┐
                        │   landing_pages     │
                        ├─────────────────────┤
                        │ PK: id              │
                        │ FK: nfc_card_id     │
                        │ - name, title, bio  │
                        │ - profile_image     │
                        │ - company_logo      │
                        │ - social_links(JSON)│
                        │ - stats (JSON)      │
                        │ - theme, design     │
                        └─────────────────────┘
```

### 关系约束说明

| 关系                      | 约束        | 说明                        |
| ------------------------- | ----------- | --------------------------- |
| users → profiles          | 1:1, UNIQUE | `profiles.user_id` 必须唯一 |
| users → nfc_cards         | 1:N         | 一个用户可以有多张 NFC 卡   |
| nfc_cards → landing_pages | 1:1         | 一张卡只有一个 Landing Page |

---

## 🎯 总结

### 正确的数据结构关系

#### ✅ 一个账户的数据组成

```
1 个 User Account (users 表)
    │
    ├─── 1 个 Profile (profiles 表) ⚡ UNIQUE
    │    └─ 固定的个人资料
    │       - slug (个人 URL)
    │       - 基本信息
    │
    └─── N 张 NFC Cards (nfc_cards 表) 🎴 可以有多张
         ├─ Card 1 → 1 个 Landing Page
         ├─ Card 2 → 1 个 Landing Page
         └─ Card 3 → 1 个 Landing Page
```

#### 数据层次说明

1. **用户层** (users 表)

   - 账户基本信息
   - 订阅信息
   - 登录凭证

2. **个人资料层** (profiles 表) - **1:1 关系**

   - ⚠️ **重要**: 一个用户只有一个 profile
   - `user_id` 有 UNIQUE 约束
   - 用于存储固定的个人资料
   - 用于生成个人主页 URL (slug)

3. **卡片层** (nfc_cards 表) - **1:N 关系**

   - 一个用户可以订购**多张** NFC 卡
   - 每张卡独立管理
   - 每张卡可以是不同的订阅计划

4. **内容层** (landing_pages 表) - **1:1 关系**
   - 每张 NFC 卡有**一个** Landing Page
   - 存储该卡的个性化内容
   - 设计设置、社交链接等

### 主要数据流

```
用户注册
    → users 表
    → 自动创建 profiles 表 (1:1)

订购 NFC 卡
    → nfc_cards 表 (可以有多张)

编辑 Landing Page
    → landing_pages 表 (每张卡一个)

上传图片
    → storage/app/public/
    → landing_pages 表 (保存路径)
```

### 关键约束

- ✅ `profiles.user_id` = **UNIQUE** (一个用户一个 profile)
- ✅ `users` → `nfc_cards` = **1:N** (一个用户多张卡)
- ✅ `nfc_cards` → `landing_pages` = **1:1** (一张卡一个页面)

---

**更新日期**: 2025-11-14
**版本**: 2.0.0 - 添加了 profiles.user_id UNIQUE 约束
