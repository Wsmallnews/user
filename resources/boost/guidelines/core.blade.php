## User 包（wsmallnews/user）

`wsmallnews/user` 是用户管理插件，提供用户认证、注册、个人资料管理、双因素认证和地址管理。命名空间根为 `Wsmallnews\User`，Blade 视图前缀为 `sn-user`，配置文件为 `config/sn-user.php`。

### 核心架构

- **UserResource**：Filament 资源，使用 `CanBeConfigured` 支持插件配置覆盖
- **Livewire 组件**：完整的认证流程（登录/注册/找回密码/邮箱验证）+ 设置页面（个人资料/密码/双因素认证）
- **Address 模型**：继承 `SupportModel`，支持省市区三级联动
- **双因素认证**：基于 TOTP 的 2FA，支持恢复码

### User 资源

继承 `Wsmallnews\User\Filament\Resources\Users\BaseResource`：

```php
use Wsmallnews\User\Filament\Resources\Users\BaseResource;

// BaseResource 已提供：
// - getModel() → Utils::getUserModel()
// - form() → UserForm（含 FormComponents::plainImageUpload）
// - table() → UserTable（含 ActionComponents、FilterComponents、CauserTimelineAction）
// - 图标、slug、导航排序、翻译标签
```

可配置的具体实现：

```php
use Wsmallnews\User\Filament\Resources\Users\UserResource;

// 在 PanelProvider 中注册
$panel->resources([UserResource::class]);
```

### Address 模型

`Wsmallnews\User\Models\Address` 继承 `SupportModel`，用于用户地址管理：

```php
use Wsmallnews\User\Models\Address;

// 使用 DistrictSelect 组件实现省市区三级联动
// 模型字段：province_name, province_id, city_name, city_id, district_name, district_id
```

### Livewire 组件

#### 认证组件

| 组件 | 注册名 | 说明 |
|---|---|---|
| `Livewire\Components\Auth\Login` | `sn-user-login` | 登录 |
| `Livewire\Components\Auth\Register` | `sn-user-register` | 注册 |
| `Livewire\Components\Auth\ForgotPassword` | `sn-user-forgot-password` | 找回密码 |
| `Livewire\Components\Auth\ResetPassword` | `sn-user-reset-password` | 重置密码 |
| `Livewire\Components\Auth\VerifyEmail` | `sn-user-verify-email` | 邮箱验证 |
| `Livewire\Components\Auth\ConfirmPassword` | `sn-user-confirm-password` | 密码确认 |

#### 设置组件

| 组件 | 注册名 | 说明 |
|---|---|---|
| `Livewire\Components\Settings\Profile` | `sn-user-settings-profile` | 个人资料编辑 |
| `Livewire\Components\Settings\Password` | `sn-user-settings-password` | 密码修改 |
| `Livewire\Components\Settings\TwoFactor` | `sn-user-settings-two-factor` | 双因素认证管理 |

#### 用户展示组件

| 组件 | 注册名 | 说明 |
|---|---|---|
| `Livewire\Components\User\Profile` | `sn-user-profile` | 用户资料展示 |
| `Livewire\Components\User\Menu` | `sn-user-menu` | 用户菜单 |
| `Livewire\Components\User\SidebarMenu` | `sn-user-sidebar-menu` | 侧边栏菜单 |

所有组件继承 `Wsmallnews\User\Livewire\Components\Base`（→ `Wsmallnews\Support\Livewire\Base`）。

### 双因素认证

配置 `sn-user.two_factor` 控制 2FA 行为：

```php
'two_factor' => [
    'enabled' => true,      // 是否启用
    'confirm' => true,      // 启用时必须确认一次
    'window' => 1,          // 验证窗口（分钟）
],
```

相关 Actions：
- `EnableTwoFactorAuthentication` — 启用 2FA
- `ConfirmTwoFactorAuthentication` — 确认 2FA
- `DisableTwoFactorAuthentication` — 禁用 2FA
- `GenerateNewRecoveryCodes` — 生成新恢复码

### 中间件

| 中间件 | 说明 |
|---|---|
| `Authenticate` | 认证中间件 |
| `EnsureEmailIsVerified` | 邮箱验证检查 |
| `EnsureUserIsActive` | 用户状态检查 |
| `RedirectIfAuthenticated` | 已登录重定向 |
| `RequirePassword` | 密码确认要求 |

### 路由

配置 `sn-user.routes` 控制前端路由：

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'user',         // URL 前缀
    'name' => 'sn-user.',       // 路由名称前缀
    'middleware' => ['web'],
    'uri' => [
        'login' => 'login',
        'register' => 'register',
        'profile' => 'profile',
        // ...
    ],
],
```

路由名称前缀为 `sn-user.`，使用 `Utils::route('login')` 生成完整路由名。

### 主题配置

配置 `sn-user.themes` 控制前端主题：

```php
'themes' => [
    'dark_mode' => true,                    // 启用暗黑模式
    'default_dark_mode' => 'system',        // 默认模式（system/light/dark）
    'dark_mode_forced' => false,            // 强制暗黑主题
    'layout' => 'sn-user::components.layouts.app',
    'page_container' => 'sn-user::container.page',
    'view_namespace' => 'sn-user::livewire.',
],
```

### Utils 工具类

`Wsmallnews\User\Support\Utils` — 全部为静态方法：

| 方法 | 说明 |
|---|---|
| `getConfig(?string $name, $default)` | 读取 `sn-user` 配置（dot notation） |
| `getPanelRegister($type)` | 获取面板注册配置（pages/resources） |
| `getModel(string $name, bool $shouldException = true)` | 获取配置的模型类名 |
| `getUserModel()` | `getModel('user')` 快捷方式 |
| `getFileDirectory(?string $type)` | 获取文件目录（自动追加日期） |
| `getDefaultDarkMode()` | 获取默认暗黑模式设置 |
| `hasDarkMode()` | 是否启用暗黑模式 |
| `hasDarkModeForced()` | 是否强制暗黑主题 |
| `getLayout()` | 获取当前布局视图 |
| `getPageContainer()` | 获取页面容器视图 |
| `getViewNamespace()` | 获取视图命名空间 |
| `route($name, $params, $absolute)` | 用户内部路由（自动添加路由前缀 + 租户参数） |

### 正确命名空间速查

| 类别 | 命名空间 |
|---|---|
| UserResource | `Wsmallnews\User\Filament\Resources\Users\UserResource` |
| BaseResource | `Wsmallnews\User\Filament\Resources\Users\BaseResource` |
| UserPlugin | `Wsmallnews\User\UserPlugin` |
| UserConfig | `Wsmallnews\User\UserConfig` |
| Address 模型 | `Wsmallnews\User\Models\Address` |
| Gender 枚举 | `Wsmallnews\User\Enums\Gender` |
| Status 枚举 | `Wsmallnews\User\Enums\Status` |
| Utils | `Wsmallnews\User\Support\Utils` |
| Facade | `Wsmallnews\User\Facades\User` |
| SidebarMenuRegistry | `Wsmallnews\User\SidebarMenuRegistry` |
| ServiceProvider | `Wsmallnews\User\UserServiceProvider` |

### 常见错误

- **`CanPagination` 已包含 `WithPagination`**，不要在 Livewire 组件中重复 `use WithPagination`。
- **`Utils` 所有方法都是静态的**，使用 `Utils::getConfig()` 而非 `(new Utils)->getConfig()`。
- **`Utils::getModel()` 默认会抛异常**，传递 `false` 作为第二个参数以允许返回 `null`。
- **Address 模型使用 DistrictSelect 组件**，确保模型中存在 `province_name/id`、`city_name/id`、`district_name/id` 字段。
- **路由名称带前缀**，使用 `Utils::route('login')` 而非直接写 `'sn-user.login'`。
