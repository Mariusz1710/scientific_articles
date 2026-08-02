# System zgłaszania artykułów naukowych

Aplikacja webowa do zgłaszania, recenzowania i publikowania artykułów naukowych,
zrealizowana w Symfony (backend + Twig), PostgreSQL, Docker, PHPUnit i
monitoringiem Prometheus/Grafana — zgodnie z wymaganiami projektu.

## 1. Architektura

```
                ┌──────────────┐        ┌──────────────┐
   przeglądarka │    nginx     │──9000──▶│   php-fpm    │──▶ PostgreSQL
                │ (port 8080)  │        │  (Symfony)   │
                └──────────────┘        └──────┬───────┘
                                                 │ /metrics
                                          ┌──────▼───────┐      ┌──────────┐
                                          │  Prometheus   │─────▶│ Grafana  │
                                          │ (port 9090)   │      │(port 3000)│
                                          └──────────────┘      └──────────┘
```

- **Frontend**: server-rendered Twig (Symfony), prosty, semantyczny HTML/CSS bez
  zależności od zewnętrznego frameworka JS — łatwe do konteneryzacji i testowania.
- **Backend**: Symfony 7.1 (PHP 8.3), architektura MVC z warstwą usług
  (`src/Service`) oddzielającą logikę biznesową od kontrolerów.
- **Baza danych**: PostgreSQL 16, mapowanie obiektowo-relacyjne przez Doctrine ORM
  (atrybuty PHP), migracje w `migrations/`.
- **Docker**: osobne kontenery `php` (php-fpm), `nginx`, `database` (Postgres),
  `prometheus`, `grafana` — połączone wspólną siecią `app_network`.
- **Testy**: PHPUnit — testy jednostkowe (`tests/Unit`) dla logiki domenowej
  (encje, serwisy) i testy funkcjonalne (`tests/Functional`) dla kontrolerów
  (WebTestCase, klient HTTP w pamięci).
- **Monitoring**: endpoint `/metrics` (format tekstowy Prometheusa) i `/health`
  (health-check gotowości bazy danych), scrapowane przez Prometheusa i
  wizualizowane w gotowym dashboardzie Grafany.

## 2. Moduły funkcjonalne (wg wymagań)

| Wymaganie | Realizacja |
|---|---|
| Logowanie/rejestracja | `SecurityController`, `RegistrationController`, `security.yaml` (form login + hasła hashowane `auto`) |
| Zgłaszanie artykułów (.tex/.pdf/.docx) | `ArticleController`, `FileUploadService`, `ArticleType` |
| Edycja przed przesłaniem | `Article::isEditable()` — edycja dozwolona tylko w statusie `wersja robocza` |
| Przypisywanie recenzentów | `ReviewController::assignReviewer()` (rola `ROLE_EDITOR`) |
| Recenzje i oceny | `Review`, `ReviewFormType`, `ReviewController::show()` |
| Statusy artykułów | `App\Enum\ArticleStatus` (wersja robocza, zgłoszony, w recenzji, zaakceptowany, odrzucony, opublikowany) |
| Powiadomienia | `Notification`, `NotificationService`, wyświetlane na pulpicie (`dashboard.html.twig`) |
| Zatwierdzanie do publikacji | `PublicationController` (rola `ROLE_EDITOR`, tzw. "opiekun") |
| Wersja do druku PDF | `PdfExportService` (biblioteka mPDF) |
| Wyszukiwanie/filtrowanie | `SearchController`, `ArticleRepository::search()` |
| Eksport CSV/PDF | `ExportController`, `CsvExportService`, `PdfExportService` |
| Statystyki | `StatsController` (widok HTML + `/stats.json`), metryki Prometheus na `/metrics` |

### Role i uprawnienia

`ROLE_AUTHOR` (domyślna) → `ROLE_REVIEWER` → `ROLE_EDITOR` (opiekun/redakcja) → `ROLE_ADMIN`
(hierarchia w `security.yaml` — wyższa rola dziedziczy uprawnienia niższych).

## 3. Uruchomienie

```bash
cp .env.example .env        # początkowa konfiguracja z wartościami domyślnymi
docker compose build
docker compose up -d
```

> Katalog projektu jest montowany do kontenera `php` na żywo (bind mount), więc
> `vendor/` zainstalowany podczas `docker compose build` bywa przez to przesłonięty.
> Kontener sam to wykrywa przy starcie i doinstalowuje zależności automatycznie
> (`docker/php/entrypoint.sh`) — obserwuj `docker compose logs -f php` i poczekaj,
> aż zobaczysz, że `composer install` się zakończył, zanim odpalisz komendy `exec`.

```bash
# Pierwsze uruchomienie: schemat bazy i (opcjonalnie) dane demo
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php bin/console doctrine:fixtures:load --no-interaction
```

- Aplikacja: http://localhost:8080
- Prometheus: http://localhost:9090
- Grafana: http://localhost:3000 (login: `admin` / `admin`, dashboard "System
  zgłaszania artykułów naukowych" wgrany automatycznie)

Konta demo (hasło dla wszystkich: `password123`):
`admin@uczelnia.pl`, `opiekun@uczelnia.pl`, `recenzent1@uczelnia.pl`,
`recenzent2@uczelnia.pl`, `autor@uczelnia.pl`.

### Testy

```bash
docker compose exec php bin/console doctrine:migrations:migrate --env=test --no-interaction
docker compose exec php vendor/bin/phpunit
```

## 5. Struktura repozytorium

```
src/Controller/   - kontrolery HTTP (routing przez atrybuty PHP)
src/Entity/       - encje Doctrine (User, Article, ArticleVersion, Review, Notification, Category)
src/Enum/         - statusy artykułu, decyzje recenzenta, formaty plików
src/Form/         - formularze Symfony (rejestracja, zgłoszenie, recenzja)
src/Repository/   - zapytania Doctrine (w tym wyszukiwanie/filtrowanie i statystyki)
src/Security/     - CasAuthenticator (logowanie uczelniane)
src/Service/      - logika domenowa: upload plików, PDF, CSV, powiadomienia
templates/        - widoki Twig
migrations/       - migracje schematu bazy danych
tests/Unit/       - testy jednostkowe
tests/Functional/ - testy funkcjonalne (WebTestCase)
monitoring/       - konfiguracja Prometheus + Grafana (datasource, dashboard)
docker/           - Dockerfile pomocnicze pliki (nginx.conf, php.ini)
```

## 6. Do dopisania w sprawozdaniu (praca dyplomowa, szablon LaTeX)

README pokrywa "Opis architektury" i częściowo "Opis implementacji" —
w sprawozdaniu warto rozwinąć: **Przegląd istniejących rozwiązań** (np.
OJS/Open Journal Systems, EasyChair, Editorial Manager — trzy realne systemy
do porównania), zrzuty ekranu, wyniki testów, oraz wnioski/możliwości
rozwoju (np. pełna integracja SAML2/PIONIER.Id, e-mail w `NotificationService`,
wersjonowanie recenzji podwójnie ślepej próby).
