# AGENTS.md

## Project Snapshot
- Stack: Laravel 12 (PHP 8.2) + Inertia + Vue 3 + Vite; queue-backed matching pipeline.
- Primary product flow: import Upwork jobs, evaluate user feeds, send Telegram notifications.
- Secondary user flow: compose cover-letter prompts from saved AI settings (`resources/js/pages/settings/Ai.vue` + `app/Http/Controllers/JobController.php`).

## End-to-End Data Flow (read these first)
- Scheduler: `routes/console.php` runs `app:fetch-jobs` using `config('app.fetch_jobs_schedule')` (default `everyTwoMinutes`) and `app:cleanup-old-jobs` daily.
- Import command: `app/Console/Commands/FetchUpworkJobs.php` calls `UpworkService::searchJobs()`, persists via `UpworkJob::createFromUpworkArray()`.
- Duplicate handling is intentional: import loop `break`s on `UniqueConstraintViolationException` (assumes reverse-chronological API results).
- Matching fan-out: each new job dispatches `FindMatchingUsers` (`app/Jobs/FindMatchingUsers.php`) to queue `default`.
- Notification path: `FindMatchingUsers` loads active feeds in `chunk(20)`, checks `Feed::matchesJob()`, then calls `TelegramNotificationService::sendJobNotification()`.
- Cover-letter path: Telegram inline `web_app` button opens `GET /job/{id}/cover-letter`; `JobController::coverLetter()` injects `{{applicant_profile}}` and `{{job_details}}` from `user_settings` values.

## Core Domain Rules
- Feed matching (`app/Models/Feed.php`) is strict AND across rules: if any rule fails, the feed does not match.
- Category filter is optional, but if categories are selected then job `upwork_category_id` must be in the feed’s pivot set.
- Rule schema used by backend and UI:
  - `{"keywords":["laravel","vue"],"location":["title","description"],"condition":"all"}`
  - `condition` supports `any | all | none`; `location` supports `title | description | category`.

## Integrations and Boundaries
- Upwork OAuth + GraphQL: `app/Services/UpworkProvider.php` + `app/Services/UpworkService.php`.
- Upwork token lifecycle is cache-driven (`Cache::put('upwork_token', ...)`) with refresh in `getValidToken()`.
- Job mapping contract is centralized in `app/Models/UpworkJob.php` (`subcategory` slug -> `upwork_category_id`).
- Telegram send formatting lives in `app/Services/TelegramNotificationService.php`; message uses HTML and truncates to Telegram 4000-char limit.
- Telegram login/auth path is in `app/Http/Controllers/Auth/TelegramAuthController.php` (`POST /login/telegram`).
- Telegram webhook boundary: `GET /setup` registers webhook and incoming updates land on `POST /{TELEGRAM_BOT_TOKEN}/webhook`; CSRF is explicitly exempted in `bootstrap/app.php`.

## Local Workflow (repo-specific)
- One-command dev loop from `composer.json`:
  - `composer run dev` starts `php artisan serve`, `queue:listen --tries=1`, `pail`, and `npm run dev` concurrently.
- If running services separately, ensure queue + scheduler are active for real behavior:
  - `php artisan queue:work`
  - `php artisan schedule:work`
- Tests: `composer test` (clears config first, then runs Pest via `php artisan test`).
- Lint/style commands used by CI: `vendor/bin/pint`, `npm run format`, and `npm run lint`.

## Conventions to Preserve
- User-owned resources are scoped by `auth()->id()` in controllers (see `app/Http/Controllers/FeedController.php`).
- Validation is mixed: starter-kit auth/settings flows use FormRequests (`app/Http/Requests/Auth/LoginRequest.php`, `app/Http/Requests/Settings/ProfileUpdateRequest.php`), while feed/AI flows keep inline `$request->validate(...)`.
- Inertia pages resolve by glob in `resources/js/app.ts`; internal nav should use Inertia patterns.
- Feed CRUD uses resource routing plus `PATCH /feeds/{id}/toggle` (`routes/web.php`).

## Known Gotchas (from code, not assumptions)
- Schedule cadence is config-driven (`FETCH_JOBS_SCHEDULE`), not hardcoded.
- Cleanup command deletes `UpworkJob` rows older than 24h (`app/Console/Commands/CleanupOldJobs.php`), so historical jobs are intentionally short-lived.
- Config naming differs across docs; code reads Telegram token from `config('telegram.bots.mybot.token')` (env key in `config/telegram.php` is `TELEGRAM_BOT_TOKEN`).
- `resources/js/pages/CoverLetter.vue` shows an error state unless both `applicant_profile` and `cover_letter_prompt` are saved in `/settings/ai`.

