# Betting Service

Мини-сервис для ставок с поддержкой аутентификации, идемпотентности и защитой от двойного списания.

---

## 🚀 Запуск проекта

### Предварительные требования
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/)

### 1. Клонировать репозиторий
```bash
git clone https://gitlab.com/your-org/betting-service.git
cd betting-service
```

### 2. Настроить окружение
Скопировать пример `.env`:
```bash
cd backend
cp .env.example .env
cd ../frontend
cp .env.example .env
cd ..
```
### 2.1. Установить зависимости
```bash
npm install
```

Проверить переменные:
```dotenv
APP_KEY=base64:...
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=bets
DB_USERNAME=app
DB_PASSWORD=secret

HMAC_SECRET=dev_hmac_secret
```

Для тестов Laravel автоматически использует `.env.testing`  
(скопируй его на основе `.env` и пропиши `APP_ENV=testing`, `DB_DATABASE=bets_test`).

### 3. Запуск контейнеров
```bash
docker-compose up -d --build
```

### 4. Сгенерить ключ b применить миграции и сиды
```bash
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate --seed
```

### 5. Запустить тесты
```bash
docker-compose exec backend php artisan test
```

---

## 🔑 Основные архитектурные решения

### Аутентификация
- Используется **Laravel Sanctum**.
- Вход через `/api/login` с email/паролем.
- Клиент получает Bearer-токен, который хранится в `localStorage` и отправляется в каждом запросе.

### Подпись запросов (HMAC)
- Каждый запрос подписывается заголовками:
    - `X-Signature` — HMAC SHA256 от тела запроса.
    - `X-Timestamp` — текущий timestamp.
- Middleware `HmacSignatureMiddleware` проверяет подпись и пишет в `fraud_logs` при несоответствии.
- В тестах HMAC отключается автоматически (по `APP_ENV=testing`).

### Идемпотентность
- Для всех `POST /api/bets` требуется заголовок `Idempotency-Key`.
- Повторный запрос с тем же ключом не создаёт новую ставку, а возвращает уже существующую.
- Реализовано в `IdempotencyMiddleware`.

### Защита от «double spend»
- В `BettingService` используется транзакция с `SELECT ... FOR UPDATE`, чтобы при одновременных запросах нельзя было превысить баланс.
- Баланс списывается атомарно, вместе с созданием ставки и записью в `ledger_transactions`.

### Rate limiting
- Для ставок действует лимит `10 запросов в минуту` (middleware `throttle:bets`).

### Fraud логирование
- Любые подозрительные действия (`missing_signature`, `bad_signature`, `bet_rejected`) пишутся в таблицу `fraud_logs`.

---

## 🖥️ Frontend (Vue 3)

- Вход через `LoginForm.vue` — получает токен и сохраняет его.
- `BetForm.vue`:
    - показывает список событий и доступные исходы,
    - валидирует баланс и сумму,
    - создаёт ставку с уникальным `Idempotency-Key`.

---

## ✅ Тесты

Основные кейсы покрыты в `tests/Feature/BettingTest.php`:
- успешная ставка и списание баланса;
- ставка больше баланса → ошибка 422;
- идемпотентность (повторный запрос не дублирует ставку);
- проверка валидности исхода;
- защита от двойного списания при параллельных запросах.

Запуск:
```bash
php artisan test
```

---

## 📦 Стек технологий
- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: Vue 3 + Axios
- **DB**: MySQL 8
- **Auth**: Laravel Sanctum
- **Infra**: Docker + Docker Compose
