# CLAUDE.md — Project Instructions for Claude Code

## Commit Message Convention

Use **Conventional Commits** with structured bullet-point body:

```
<type>: <short summary in English>

- What: <what was added/changed/fixed>
- Why: <reason or problem being solved>
- Skip: <anything intentionally excluded> (optional)
- Note: <caveats, side effects, or follow-ups> (optional)
```

### Types
- `feat` — new feature or capability
- `fix` — bug fix
- `refactor` — code restructure without behavior change
- `chore` — tooling, CI, config, dependencies
- `docs` — documentation only
- `test` — adding or fixing tests
- `style` — formatting, no logic change

### Rules
- Title line: max 72 characters, English, imperative mood
- Body bullets: use Thai or English (match the context)
- Always include `What` and `Why` bullets
- `Skip` and `Note` bullets only when relevant
- No emoji in commit messages

### Example
```
feat: add smart seeding step to deploy.sh

- What: เพิ่ม run_seeders() สำหรับ seed ตารางที่จำเป็นอัตโนมัติ
- Why: deploy ครั้งแรกไม่มีข้อมูล lottery_types, bet_types, settings
- Skip: DemoSeeder, AdminUserSeeder, TestDataSeeder (ไม่ auto-run)
```

## Project Overview

- **Stack**: Laravel 11 + Blade + Alpine.js + Tailwind CSS + Vite
- **Language**: Thai UI, English code
- **Auth**: Firebase Phone OTP (members), Sanctum (API), session (admin)
- **Admin**: Custom admin panel at `/admin/*` with `admin.only` middleware
