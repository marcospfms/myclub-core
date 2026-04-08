# Napkin Runbook

## Curation Rules
- Re-prioritize on every read.
- Keep recurring, high-value notes only.
- Max 10 items per category.
- Each item includes date + "Do instead".

## Execution & Validation (Highest Priority)
1. **[2026-04-08] Laravel CLI must use WAMP PHP 8.4**
   Do instead: run Artisan and test commands with `/mnt/c/wamp64/bin/php/php8.4.19/php.exe` instead of the WSL `php` binary.

## Shell & Command Reliability
1. **[2026-04-08] Keep commits in pt-BR with fixed prefixes**
   Do instead: use only `Feat: descrição curta` or `Fix: descrição curta` in Portuguese for every commit message.

## Domain Behavior Guardrails
1. **[2026-04-08] Catalog display text is not stored as final UI copy**
   Do instead: persist stable identifiers plus `label_key` / `description_key` / `icon` and resolve translations in each frontend.

## User Directives
1. **[2026-04-08] Agrupar mudanças por funcionalidade**
   Do instead: prefer small commits scoped to one feature block and update roadmap/docs together with implementation.
