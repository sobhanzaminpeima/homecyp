# Easy Cyprus — معرفی پروژه

**Easy Cyprus** یک اپلیکیشن وب برای کشف کسب‌وکارها (رستوران، فروشگاه، خدمات و ...) در قبرس شمالی است. کاربر شهر خودش رو انتخاب می‌کنه و می‌تونه کسب‌وکارهای اطراف، منو، نظرات و ... رو ببینه. یک پنل داشبورد هم برای صاحبان کسب‌وکار/ادمین وجود داره.

## ساختار پروژه

پروژه در `C:\Claude Projects\Easy Cyprus` قرار داره و به دو بخش مجزا تقسیم شده (مونو-ریپو ساده، نه یک ریپوی گیت واحد):

```
Easy Cyprus/
├── laravel/     ← بک‌اند (Laravel API)
├── frontend/    ← فرانت‌اند (Vue 3 SPA)
├── build/       ← فایل‌های آماده دیپلوی (zip، دامپ SQL)
└── deploy/      ← اسکریپت‌های نصب روی هاست (setup.php، INSTALL.txt)
```

⚠️ این پوشه یک گیت‌ریپازیتوری نیست (git init نشده).

## بک‌اند (`laravel/`)

- **فریمورک**: Laravel (PHP 8.2)
- **API**: زیر مسیر `routes/api.php`، کنترلرها در `app/Http/Controllers/Api/V1/` (نسخه‌بندی شده با `V1`)
  - کنترلرهای اصلی: `AuthController`, `BusinessController`, `BusinessDashboardController`, `CategoryController`, `CityController`, `DashboardMenuController`, `FavoriteController`, `HomeController`, `ReviewController`, `SettingController`, `SetupController` + یک زیرپوشه `Admin/`
- **مدل‌ها** (`app/Models/`): `Business`, `Category`, `City`, `Advertisement`, `Favorite`, `Menu`, `MenuItem`, `Package`, `Review`, `Setting`, `User`
- **دیتابیس**: MySQL، نام دیتابیس `easycyprus`
- **Seederها**: `SettingSeeder`, `CitySeeder`, `CategorySeeder`, `PackageSeeder`, `UserSeeder`, `BusinessSeeder`
- **پیکربندی**: `laravel/.env` (پورت پیش‌فرض API: `http://localhost:8000`, پیشوند API: `/api/v1`)

## فرانت‌اند (`frontend/`)

- **استک**: Vue 3 + TypeScript + Vite + Pinia + vue-router + vue-i18n + Tailwind CSS 4، به‌صورت PWA (`vite-plugin-pwa`)
- **اتصال به API**: `src/lib/api.ts` (axios) — `baseURL` از `VITE_API_URL` در `.env.development` می‌خونه (پیش‌فرض: `http://127.0.0.1:8000/api/v1`)
- **صفحات اصلی** (`src/views/`): `SplashView`, `CitySelectView`, `HomeView`, `ExploreView`, `BusinessDetailView`, `FavoritesView`, `ProfileView` + زیرپوشه‌های `admin/`, `auth/`, `dashboard/`
- **Store ها** (`src/stores/`): `auth.ts`, `city.ts`
- **پورت دیفالت دولوپمنت**: `5173`

## محیط اجرا روی این سیستم (ویندوز، بدون Docker)

- روی این کامپیوتر PHP/MySQL جداگانه نصب نیست؛ **XAMPP** در مسیر `C:\xampp` استفاده می‌شه:
  - PHP: `C:\xampp\php\php.exe`
  - MySQL: `C:\xampp\mysql\bin\mysqld.exe` / `mysql.exe`
- **Composer نصب نیست** ولی نیازی هم نیست چون `laravel/vendor` از قبل نصب شده (composer install قبلاً انجام شده).
- برای اجرا:
  1. اجرای MySQL: `C:\xampp\mysql\bin\mysqld.exe --console`
  2. ساخت دیتابیس در صورت نبود: `CREATE DATABASE easycyprus ...`
  3. مهاجرت و seed: از پوشه `laravel/` با `C:\xampp\php\php.exe artisan migrate --force` و `db:seed --force`
  4. اجرای بک‌اند: از پوشه `laravel/` با `C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000`
  5. اجرای فرانت‌اند: از پوشه `frontend/` با `npm run dev` (پورت 5173، Node از قبل نصب هست)

## نکات مهم

- `laravel/.env` حاوی `SETUP_TOKEN` و اطلاعات دیتابیس محلی هست — فایل حساسه، در جای عمومی به اشتراک گذاشته نشه.
- پوشه `build/` نسخه‌های آماده دیپلوی (zip و دامپ SQL دیتابیس واقعی/نمونه) رو نگه می‌داره — برای دیپلوی روی هاست از `deploy/setup.php` و `deploy/INSTALL.txt` استفاده می‌شه.
