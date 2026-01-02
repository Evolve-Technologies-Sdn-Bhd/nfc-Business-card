# NFC Business Card - Database Schema

> Generated: 2025-12-03
> 用于在另一台电脑上参考数据库结构

---

## 📋 Table of Contents

1. [users](#1-users)
2. [nfc_cards](#2-nfc_cards)
3. [landing_pages](#3-landing_pages)
4. [social_links](#4-social_links)
5. [nfc_tags](#5-nfc_tags)
6. [analytics](#6-analytics)
7. [notifications](#7-notifications)
8. [transactions](#8-transactions)
9. [subscriptions](#9-subscriptions)
10. [payment_methods](#10-payment_methods)
11. [refunds](#11-refunds)
12. [invoices](#12-invoices)
13. [activity_logs](#13-activity_logs)
14. [social_identities](#14-social_identities)
15. [legal_documents](#15-legal_documents)
16. [chatbot_questions](#16-chatbot_questions)
17. [chatbot_feedback](#17-chatbot_feedback)
18. [profile_design_options](#18-profile_design_options)

---

## 1. users

用户表 - 存储所有用户信息

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| first_name | varchar | NO | - | 名字 |
| last_name | varchar | NO | - | 姓氏 |
| email | varchar | NO | - | 邮箱（唯一） |
| email_verified_at | timestamp | YES | null | 邮箱验证时间 |
| password | varchar | NO | - | 密码（加密） |
| phone | varchar | YES | null | 电话 |
| company | varchar | YES | null | 公司名称 |
| job_title | varchar | YES | null | 职位 |
| plan | enum | NO | 'free' | 订阅计划 ['free','basic','premium','business'] |
| has_physical_card | boolean | NO | false | 是否有实体卡 |
| last_login_at | timestamp | YES | null | 最后登录时间 |
| two_factor_enabled | boolean | NO | false | 是否启用2FA |
| two_factor_secret | varchar | YES | null | 2FA密钥 |
| remember_token | varchar | YES | null | 记住登录token |
| **Subscription Fields** |
| subscription_plan | enum | NO | 'free' | 订阅计划 ['free','basic','premium','business'] |
| subscription_start_date | date | YES | null | 订阅开始日期 |
| subscription_end_date | date | YES | null | 订阅结束日期 |
| subscription_active | boolean | NO | false | 订阅是否激活 |
| stripe_customer_id | varchar | YES | null | Stripe客户ID |
| stripe_subscription_id | varchar | YES | null | Stripe订阅ID |
| **Admin Fields** |
| is_admin | boolean | NO | false | 是否管理员 |
| admin_role | varchar | YES | null | 管理员角色 (super_admin, admin, etc.) |
| admin_permissions | json | YES | null | 管理员权限 |
| last_admin_action_at | timestamp | YES | null | 最后管理员操作时间 |
| **OAuth Fields** |
| provider | varchar | YES | null | OAuth提供商 (google, apple) |
| provider_id | varchar | YES | null | OAuth用户ID |
| **Business Quota Fields** |
| total_account_slots | integer | NO | 0 | 总账户槽位 |
| total_card_quota | integer | NO | 0 | 总卡片配额 |
| parent_business_id | bigint FK | YES | null | 父业务账户ID（员工用） |
| **Other Fields** |
| settings | json | YES | null | 用户设置 |
| is_new_user | boolean | NO | false | 是否新用户 |
| account_image | varchar | YES | null | 账户头像 |
| last_login_device | text | YES | null | 最后登录设备信息 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

**Indexes:**
- `users_email_unique` (email)
- `users_provider_provider_id_index` (provider, provider_id)

---

## 2. nfc_cards

NFC卡表 - 存储NFC卡信息

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| user_id | bigint FK | NO | - | 用户ID |
| business_account_id | bigint FK | YES | null | 业务账户ID（配额追踪） |
| card_id | varchar | NO | - | 卡片唯一标识（唯一） |
| nfc_card_id | varchar | YES | null | 实体NFC卡ID（管理员编码）|
| card_owner | varchar | NO | - | 卡片持有人姓名 |
| billing_address | text | NO | - | 账单地址 |
| contact_number | varchar | NO | - | 联系电话 |
| purchase_date | date | NO | - | 购买日期 |
| expiry_date | date | YES | null | 过期日期 |
| status | enum | NO | 'active' | 状态 ['active','inactive','expired','replacement'] |
| subscription_plan | enum | NO | 'free' | 订阅计划 ['free','basic','premium','business'] |
| purchase_amount | decimal(10,2) | NO | 0.00 | 购买金额 |
| payment_method | varchar | YES | null | 支付方式 |
| shipping_address | varchar | YES | null | 送货地址 |
| tracking_number | varchar | YES | null | 物流追踪号 |
| shipped_date | date | YES | null | 发货日期 |
| delivered_date | date | YES | null | 送达日期 |
| notes | text | YES | null | 备注 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

**Indexes:**
- `nfc_cards_user_id_status_index` (user_id, status)
- `nfc_cards_nfc_card_id_index` (nfc_card_id)
- `nfc_cards_subscription_plan_index` (subscription_plan)
- `nfc_cards_business_account_id_subscription_plan_index` (business_account_id, subscription_plan)

---

## 3. landing_pages

着陆页表 - 存储NFC卡的个人资料页面数据

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| nfc_card_id | bigint FK | NO | - | 关联NFC卡ID（唯一） |
| **Basic Info** |
| name | varchar | YES | null | 姓名 |
| title | varchar | YES | null | 职位头衔 |
| qualification | varchar | YES | null | 资质 |
| bio | text | YES | null | 个人简介 |
| phone | varchar | YES | null | 电话 |
| email | varchar | YES | null | 邮箱 |
| website | varchar | YES | null | 网站 |
| address | text | YES | null | 地址 |
| profile_image | varchar | YES | null | 头像URL |
| profile_image_path | varchar | YES | null | 头像路径 |
| company_logo | varchar | YES | null | 公司Logo URL |
| company_logo_path | varchar | YES | null | 公司Logo路径 |
| location | varchar | YES | null | 位置 |
| **Company Info** |
| company_logo_text | varchar | YES | null | 公司Logo文字 |
| company_name | varchar | YES | null | 公司名称 |
| company_registration_no | varchar | YES | null | 公司注册号 |
| company_department | varchar | YES | null | 部门 |
| **Address Details** |
| address_name | varchar | YES | null | 地址名称 |
| address_street | varchar | YES | null | 街道 |
| address_area | varchar | YES | null | 区域 |
| address_city_state | varchar | YES | null | 城市/州 |
| address_country | varchar | YES | null | 国家 |
| address_map_url | text | YES | null | 地图URL |
| **JSON Fields** |
| stats | json | YES | null | 统计数据 |
| services | json | YES | null | 服务列表 |
| social_links | json | YES | null | 社交链接 |
| team_members | json | YES | null | 团队成员 |
| **Contact Methods** |
| phone_number | varchar | YES | null | 电话号码 |
| phone_label | varchar | YES | null | 电话标签 |
| email_address | varchar | YES | null | 邮箱地址 |
| email_label | varchar | YES | null | 邮箱标签 |
| whatsapp_number | varchar | YES | null | WhatsApp号码 |
| whatsapp_label | varchar | YES | null | WhatsApp标签 |
| website_url | text | YES | null | 网站URL |
| website_label | varchar | YES | null | 网站标签 |
| **Design Settings** |
| profile_style | varchar | NO | 'classic' | 个人资料样式 |
| theme | varchar | NO | 'minimal' | 主题 |
| background_color | varchar | NO | '#FFFFFF' | 背景色 |
| text_color | varchar | NO | '#000000' | 文字颜色 |
| font | varchar | NO | 'inter' | 字体 |
| button_style | varchar | NO | 'solid' | 按钮样式 |
| show_watermark | boolean | NO | true | 显示水印 |
| **Status** |
| is_active | boolean | NO | true | 是否激活 |
| settings | json | YES | null | 其他设置 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 4. social_links

社交链接表 - 存储着陆页的社交链接

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| landing_page_id | bigint FK | NO | - | 关联着陆页ID |
| platform | varchar | NO | - | 平台 (facebook, instagram, etc.) |
| url | varchar | NO | - | 链接URL |
| title | varchar | NO | - | 标题 |
| order | integer | NO | 0 | 排序 |
| is_active | boolean | NO | true | 是否激活 |
| click_count | integer | NO | 0 | 点击次数 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 5. nfc_tags

NFC标签表 - 存储NFC标签信息

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| user_id | bigint FK | NO | - | 用户ID |
| nfc_id | varchar | NO | - | NFC标签ID（唯一） |
| name | varchar | NO | 'Business Card' | 名称 |
| status | enum | NO | 'active' | 状态 ['active','inactive'] |
| tap_count | integer | NO | 0 | 触碰次数 |
| last_tapped_at | timestamp | YES | null | 最后触碰时间 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 6. analytics

分析表 - 存储访问分析数据

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| trackable_type | varchar | NO | - | 可追踪实体类型 |
| trackable_id | bigint | NO | - | 可追踪实体ID |
| action | varchar | NO | - | 动作 (profile_view, nfc_tap, link_click) |
| ip_address | varchar | YES | null | IP地址 |
| user_agent | varchar | YES | null | 用户代理 |
| device_type | varchar | YES | null | 设备类型 |
| browser | varchar | YES | null | 浏览器 |
| platform | varchar | YES | null | 平台 |
| country | varchar | YES | null | 国家 |
| city | varchar | YES | null | 城市 |
| referrer | varchar | YES | null | 来源 |
| data | json | YES | null | 额外数据 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 7. notifications

通知表 - 存储用户通知

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| user_id | bigint FK | NO | - | 用户ID |
| type | enum | NO | - | 通知类型（见下方列表） |
| title | varchar | NO | - | 标题 |
| message | text | NO | - | 消息内容 |
| data | json | YES | null | 额外元数据 |
| attachments | json | YES | null | 附件 |
| is_read | boolean | NO | false | 是否已读 |
| read_at | timestamp | YES | null | 阅读时间 |
| pinned | boolean | NO | false | 是否置顶 |
| priority | enum | NO | 'normal' | 优先级 ['low','normal','high','urgent'] |
| action_url | varchar | YES | null | 操作URL |
| action_text | varchar | YES | null | 操作文本 |
| icon | varchar | YES | null | 图标 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

**Notification Types:**
- `registration_success` - 注册成功
- `email_verification` - 邮箱验证
- `login_new_device` - 新设备登录
- `profile_updated` - 资料更新
- `link_milestone` - 链接里程碑
- `landing_page_viewed` - 着陆页被访问
- `contact_request` - 联系请求
- `subscription_upgrade` - 订阅升级
- `payment_successful` - 支付成功
- `payment_failed` - 支付失败
- `account_warning` - 账户警告
- `password_changed` - 密码更改
- `nfc_card_purchased` - NFC卡购买
- `nfc_card_delivered` - NFC卡送达
- `nfc_card_linked` - NFC卡已链接
- `nfc_card_activated` - NFC卡激活
- `nfc_card_expired` - NFC卡过期
- `app_update` - 应用更新
- `system_message` - 系统消息
- `admin_announcement` - 管理员公告
- `business_bulk_order_placed` - 批量订单
- `employee_password_reset` - 员工密码重置
- `business_card_order_request` - 业务卡订购请求

**Indexes:**
- `notifications_user_id_is_read_index` (user_id, is_read)
- `notifications_user_id_created_at_index` (user_id, created_at)
- `notifications_type_index` (type)
- `notifications_user_id_pinned_created_at_index` (user_id, pinned, created_at)

---

## 8. transactions

交易表 - 存储支付交易记录

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| transaction_id | varchar | NO | - | 交易ID（唯一） |
| user_id | bigint FK | NO | - | 用户ID |
| payment_method_id | bigint FK | YES | null | 支付方式ID |
| type | enum | NO | - | 类型 ['payment','refund','subscription'] |
| payment_rail | enum | NO | - | 支付渠道 ['card','fpx','duitnow','ewallet','manual_bank'] |
| provider | varchar | NO | - | 提供商 (stripe, billplz) |
| provider_transaction_id | varchar | YES | null | 提供商交易ID |
| amount | decimal(10,2) | NO | - | 金额 |
| currency | varchar(3) | NO | 'MYR' | 货币 |
| fee | decimal(10,2) | NO | 0 | 手续费 |
| net_amount | decimal(10,2) | NO | - | 净额 |
| status | enum | NO | - | 状态（见下方列表） |
| subscription_id | bigint FK | YES | null | 订阅ID |
| is_recurring | boolean | NO | false | 是否循环 |
| card_last4 | varchar | YES | null | 卡号后4位 |
| bank_name | varchar | YES | null | 银行名称 |
| ewallet_type | varchar | YES | null | 电子钱包类型 |
| bank_reference_code | varchar | YES | null | 银行参考号 |
| payment_proof_url | varchar | YES | null | 支付凭证URL |
| payment_proof_uploaded_at | timestamp | YES | null | 凭证上传时间 |
| verified_by | bigint FK | YES | null | 验证人ID |
| verified_at | timestamp | YES | null | 验证时间 |
| failure_code | varchar | YES | null | 失败代码 |
| failure_message | text | YES | null | 失败消息 |
| three_ds_status | varchar | YES | null | 3DS状态 |
| client_secret | text | YES | null | 客户端密钥 |
| description | text | YES | null | 描述 |
| metadata | json | YES | null | 元数据 |
| ip_address | varchar | YES | null | IP地址 |
| user_agent | text | YES | null | 用户代理 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |
| deleted_at | timestamp | YES | null | 软删除时间 |

**Transaction Status:**
- `pending` - 待处理
- `processing` - 处理中
- `requires_action` - 需要操作（3DS验证）
- `succeeded` - 成功
- `failed` - 失败
- `cancelled` - 已取消
- `refunded` - 已退款
- `partially_refunded` - 部分退款

---

## 9. subscriptions

订阅表 - 存储用户订阅信息

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| user_id | bigint FK | NO | - | 用户ID |
| payment_method_id | bigint FK | YES | null | 支付方式ID |
| subscription_id | varchar | NO | - | 订阅ID（唯一） |
| provider | varchar | NO | - | 提供商 |
| provider_subscription_id | varchar | YES | null | 提供商订阅ID |
| plan_name | varchar | NO | - | 计划名称 |
| plan_type | enum | NO | - | 计划类型 ['basic','business','premium'] |
| amount | decimal(10,2) | NO | - | 金额 |
| currency | varchar(3) | NO | 'MYR' | 货币 |
| interval | enum | NO | 'monthly' | 间隔 ['monthly','yearly'] |
| status | enum | NO | - | 状态 ['active','past_due','cancelled','suspended','expired'] |
| current_period_start | timestamp | YES | null | 当前周期开始 |
| current_period_end | timestamp | YES | null | 当前周期结束 |
| next_billing_date | timestamp | YES | null | 下次扣款日期 |
| trial_ends_at | timestamp | YES | null | 试用结束时间 |
| cancelled_at | timestamp | YES | null | 取消时间 |
| cancellation_reason | varchar | YES | null | 取消原因 |
| metadata | json | YES | null | 元数据 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |
| deleted_at | timestamp | YES | null | 软删除时间 |

---

## 10. payment_methods

支付方式表 - 存储用户支付方式

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| user_id | bigint FK | NO | - | 用户ID |
| type | enum | NO | - | 类型 ['card','bank','ewallet'] |
| provider | varchar | NO | - | 提供商 |
| provider_payment_method_id | varchar | YES | null | 提供商支付方式ID |
| card_brand | varchar | YES | null | 卡品牌 (visa, mastercard) |
| card_last4 | varchar | YES | null | 卡号后4位 |
| card_exp_month | varchar | YES | null | 卡过期月份 |
| card_exp_year | varchar | YES | null | 卡过期年份 |
| card_fingerprint | varchar | YES | null | 卡指纹 |
| bank_name | varchar | YES | null | 银行名称 |
| bank_account_last4 | varchar | YES | null | 银行账号后4位 |
| ewallet_type | varchar | YES | null | 电子钱包类型 |
| ewallet_account_id | varchar | YES | null | 电子钱包账户ID |
| is_default | boolean | NO | false | 是否默认 |
| is_verified | boolean | NO | false | 是否已验证 |
| metadata | json | YES | null | 元数据 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |
| deleted_at | timestamp | YES | null | 软删除时间 |

---

## 11. refunds

退款表 - 存储退款记录

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| refund_id | varchar | NO | - | 退款ID（唯一） |
| transaction_id | bigint FK | NO | - | 交易ID |
| user_id | bigint FK | NO | - | 用户ID |
| provider | varchar | NO | - | 提供商 |
| provider_refund_id | varchar | YES | null | 提供商退款ID |
| amount | decimal(10,2) | NO | - | 金额 |
| currency | varchar(3) | NO | 'MYR' | 货币 |
| status | enum | NO | - | 状态 ['pending','succeeded','failed','cancelled'] |
| reason | varchar | YES | null | 原因 |
| notes | text | YES | null | 备注 |
| processed_by | bigint FK | YES | null | 处理人ID |
| processed_at | timestamp | YES | null | 处理时间 |
| failure_code | varchar | YES | null | 失败代码 |
| failure_message | text | YES | null | 失败消息 |
| metadata | json | YES | null | 元数据 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 12. invoices

发票表 - 存储发票信息

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| invoice_number | varchar | NO | - | 发票号（唯一，如 INV-2025-00001） |
| user_id | bigint FK | NO | - | 用户ID |
| transaction_id | bigint FK | YES | null | 交易ID |
| subtotal | decimal(10,2) | NO | - | 小计 |
| tax_rate | decimal(5,2) | NO | 0 | 税率 |
| tax_amount | decimal(10,2) | NO | 0 | 税额 |
| discount_amount | decimal(10,2) | NO | 0 | 折扣 |
| total_amount | decimal(10,2) | NO | - | 总金额 |
| currency | varchar(3) | NO | 'MYR' | 货币 |
| line_items | json | NO | - | 明细项 [{description, quantity, unit_price, amount}] |
| status | enum | NO | 'draft' | 状态 ['draft','issued','paid','cancelled','refunded'] |
| version | integer | NO | 1 | 版本号 |
| parent_invoice_id | bigint FK | YES | null | 父发票ID |
| pdf_path | varchar | YES | null | PDF路径 |
| pdf_filename | varchar | YES | null | PDF文件名 |
| pdf_size | integer | YES | null | PDF大小(bytes) |
| company_details | json | YES | null | 公司信息快照 |
| billing_details | json | NO | - | 账单信息 |
| metadata | json | YES | null | 元数据 |
| notes | text | YES | null | 备注 |
| terms | text | YES | null | 付款条款 |
| issued_at | timestamp | YES | null | 开票时间 |
| paid_at | timestamp | YES | null | 支付时间 |
| cancelled_at | timestamp | YES | null | 取消时间 |
| due_date | timestamp | YES | null | 到期日 |
| issued_by | bigint FK | YES | null | 开票人ID |
| cancelled_by | bigint FK | YES | null | 取消人ID |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 13. activity_logs

活动日志表 - 存储用户活动记录

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| user_id | bigint FK | NO | - | 操作用户ID |
| business_account_id | bigint FK | YES | null | 关联业务账户ID |
| action_type | enum | NO | - | 动作类型（见下方列表） |
| action_description | varchar | NO | - | 动作描述 |
| entity_type | varchar | YES | null | 实体类型 |
| entity_id | bigint | YES | null | 实体ID |
| old_values | json | YES | null | 旧值 |
| new_values | json | YES | null | 新值 |
| metadata | json | YES | null | 元数据 |
| ip_address | varchar(45) | YES | null | IP地址 |
| user_agent | text | YES | null | 用户代理 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

**Action Types:**
- `login` - 登录
- `logout` - 登出
- `profile_updated` - 资料更新
- `password_changed` - 密码更改
- `email_changed` - 邮箱更改
- `landing_page_updated` - 着陆页更新
- `landing_page_created` - 着陆页创建
- `nfc_card_activated` - NFC卡激活
- `nfc_card_deactivated` - NFC卡停用
- `link_added` - 链接添加
- `link_updated` - 链接更新
- `link_deleted` - 链接删除
- `link_reordered` - 链接重排
- `profile_image_updated` - 头像更新
- `company_logo_updated` - 公司Logo更新
- `social_links_updated` - 社交链接更新
- `contact_info_updated` - 联系信息更新
- `card_design_updated` - 卡片设计更新
- `settings_changed` - 设置更改

---

## 14. social_identities

社交身份表 - 存储OAuth登录信息

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| user_id | bigint FK | NO | - | 用户ID |
| provider | varchar | NO | - | 提供商 (google, apple, facebook) |
| provider_id | varchar | NO | - | 提供商用户ID |
| email | varchar | YES | null | 邮箱 |
| access_token | text | YES | null | 访问令牌 |
| refresh_token | text | YES | null | 刷新令牌 |
| token_expires_at | timestamp | YES | null | 令牌过期时间 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

**Constraints:**
- `social_identities_user_id_provider_unique` (user_id, provider)

---

## 15. legal_documents

法律文档表 - 存储服务条款和隐私政策

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| type | enum | NO | - | 类型 ['terms_of_service','privacy_policy']（唯一） |
| content | text | NO | - | 内容 |
| version | varchar | NO | '1.0' | 版本 |
| effective_date | timestamp | YES | null | 生效日期 |
| updated_by | bigint FK | YES | null | 更新人ID |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 16. chatbot_questions

聊天机器人问题表 - 存储FAQ

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| question | varchar | NO | - | 问题 |
| answer | text | NO | - | 答案 |
| keywords | json | NO | - | 关键词数组 |
| priority | integer | NO | 0 | 优先级 |
| is_active | boolean | NO | true | 是否激活 |
| view_count | integer | NO | 0 | 浏览次数 |
| helpful_count | integer | NO | 0 | 有帮助次数 |
| not_helpful_count | integer | NO | 0 | 无帮助次数 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 17. chatbot_feedback

聊天机器人反馈表

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| chatbot_question_id | bigint FK | YES | null | 问题ID |
| user_id | bigint FK | YES | null | 用户ID |
| is_helpful | boolean | NO | - | 是否有帮助 |
| feedback | text | YES | null | 反馈内容 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

---

## 18. profile_design_options

个人资料设计选项表 - 存储主题、字体等设计选项

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | NO | auto | Primary key |
| type | enum | NO | - | 类型 ['theme','font','button_style','profile_style'] |
| option_id | varchar | NO | - | 选项ID (minimal, inter, solid) |
| name | varchar | NO | - | 显示名称 |
| config | json | YES | null | 配置 (颜色、类等) |
| is_active | boolean | NO | true | 是否激活 |
| is_default | boolean | NO | false | 是否默认 |
| display_order | integer | NO | 0 | 显示顺序 |
| description | text | YES | null | 描述 |
| created_at | timestamp | YES | null | 创建时间 |
| updated_at | timestamp | YES | null | 更新时间 |

**Constraints:**
- `profile_design_options_type_option_id_unique` (type, option_id)

**Default Options:**
- **Themes**: minimal, modern, creative, professional, dark
- **Fonts**: inter, poppins, roboto, playfair
- **Button Styles**: solid, outline, soft, shadow
- **Profile Styles**: classic

---

## 📊 Entity Relationships

```
users
├── nfc_cards (one-to-many) [user_id]
├── nfc_tags (one-to-many) [user_id]
├── notifications (one-to-many) [user_id]
├── transactions (one-to-many) [user_id]
├── subscriptions (one-to-many) [user_id]
├── payment_methods (one-to-many) [user_id]
├── invoices (one-to-many) [user_id]
├── activity_logs (one-to-many) [user_id]
├── social_identities (one-to-many) [user_id]
└── business_employees (self-reference via parent_business_id)

nfc_cards
└── landing_pages (one-to-one) [nfc_card_id]
    └── social_links (one-to-many) [landing_page_id]

transactions
├── refunds (one-to-many) [transaction_id]
└── invoices (one-to-many) [transaction_id]
```

---

## 🔧 Migration Commands

```bash
# 运行迁移
php artisan migrate

# 回滚迁移
php artisan migrate:rollback

# 重置并重新运行
php artisan migrate:fresh

# 查看迁移状态
php artisan migrate:status

# 生成新迁移
php artisan make:migration create_table_name_table
```

---

## 📝 Notes

1. **JSON字段**: 很多表使用JSON字段存储灵活的数据结构（如 settings, metadata, data）
2. **软删除**: transactions, subscriptions, payment_methods 使用软删除
3. **枚举类型**: 使用MySQL的ENUM类型确保数据完整性
4. **外键约束**: 大部分外键使用 `cascade` 或 `set null` 删除策略
5. **索引**: 关键查询字段都有索引优化

---

*Generated for NFC Business Card Project*
