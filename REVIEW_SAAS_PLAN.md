# ReviewBoost — MVP Plan (Email + QR Code)

## Co budujemy
SaaS do zbierania Google Reviews dla lokalnych biznesów w USA.
Flow: klient kończy wizytę → dostaje email lub skanuje QR → ocenia 1-5 → pozytywne idą na Google, negatywne do dashboardu właściciela.

MVP bez LLC, bez Twilio, bez SMS. Email + QR code only.

---

## FAZA 0 — Dziś (Sobota, dzień 1)

### Krok 1: Domena
- Wejdź na [Namecheap](https://namecheap.com) lub [Cloudflare Registrar](https://dash.cloudflare.com)
- Kup domenę .com (~$10-12/rok)
- Propozycje nazw: getreviewboost.com, reviewpilot.co, starreply.com, quickfeedback.app
- **Nie trać na to więcej niż 30 minut** — nazwa nie ma znaczenia na etapie MVP

### Krok 2: Hosting
- Załóż konto na [Hetzner Cloud](https://console.hetzner.cloud)
- Kup VPS **CX22** (2 vCPU, 4GB RAM) — €4.35/msc
- OS: Ubuntu 22.04
- Zainstaluj: nginx, PHP 8.3, PostgreSQL 16, Composer, Node.js 20, Certbot
- Albo użyj [Laravel Forge](https://forge.laravel.com) ($12/msc) jeśli nie chcesz ręcznie admina robić

### Krok 3: Repozytorium
- Załóż repo na GitHubie (private)
- `composer create-project laravel/laravel review-saas`
- Pierwszy commit, push

### Krok 4: Konta zewnętrzne (darmowe na start)
- [Stripe](https://stripe.com) — konto na polską działalność lub osobiste, waluta USD
- [Resend](https://resend.com) — darmowy tier (100 emaili/dzień, wystarczy na start)
- Opcjonalnie: [Postmark](https://postmarkapp.com) jako backup (darmowy trial)

---

## FAZA 1 — Tydzień 1-2: Core Backend

### Krok 5: Autentykacja i Tenant
- Laravel Breeze (prosty auth)
- Model `Tenant` (firma klienta) z relacją do `User`
- Middleware tenant-aware
- Struktura:

```
app/
├── Domain/
│   ├── Identity/          # User, Tenant, Registration
│   ├── Business/          # BusinessProfile, Location
│   ├── Campaign/          # ReviewRequest, Template, QRCode
│   ├── Feedback/          # Response, Rating, Routing
│   └── Analytics/         # Stats, Reports
├── Application/           # Use Cases / Application Services
└── Infrastructure/        # Repositories, External APIs
```

### Krok 6: Business Profile
- CRUD dla biznesu: nazwa, adres, Google Review link, logo
- **Skąd wziąć Google Review link:** instrukcja dla klienta — "Wyszukaj swoją firmę w Google → Share → Copy link" albo zbuduj helper który generuje link z Place ID

### Krok 7: Review Request Flow (CORE FEATURE)

**Email flow:**
1. Właściciel wpisuje email klienta w dashboardzie (lub CSV upload)
2. System wysyła email: "How was your experience at [Business]? Click to rate"
3. Klient klika → landing page z 5 gwiazdkami
4. Klient wybiera ocenę:
   - **4-5 gwiazdek** → "Thank you! Would you mind sharing this on Google?" → redirect do Google Review link
   - **1-3 gwiazdki** → "We're sorry. Tell us what happened" → formularz feedbacku → dane trafiają do dashboardu właściciela, NIE na Google

**QR Code flow:**
1. System generuje unikalny QR code dla każdego biznesu
2. Właściciel drukuje i umieszcza w sklepie/biurze
3. Klient skanuje → ten sam landing page z gwiazdkami
4. Dalej identyczny routing jak wyżej

### Krok 8: Rating Landing Page
- Prosta, ładna strona mobilna (90% ludzi skanuje telefonem)
- Logo biznesu, nazwa, 5 klikalnych gwiazdek
- Zero logowania, zero frykcji
- Musi ładować się < 1 sekundy

### Krok 9: Feedback Form
- Dla ocen 1-3: prosty formularz (textarea + opcjonalny email)
- Dane zapisywane w DB, widoczne w dashboardzie właściciela
- Powiadomienie email do właściciela o negatywnym feedbacku

---

## FAZA 2 — Tydzień 3-4: Dashboard + Płatności

### Krok 10: Dashboard właściciela
- Wysłane requesty (ile, do kogo, kiedy)
- Odpowiedzi (ile pozytywnych, ile negatywnych)
- Trend (wykres: reviews w czasie)
- Lista negatywnych feedbacków z treścią
- Prosty design — nie musisz mieć ładnego UI, musisz mieć czytelny

### Krok 11: QR Code Generator
- Generowanie QR kodu z linkiem do rating page
- Pobieranie jako PNG/PDF do druku
- Opcja: customizacja kolorów (nice to have, nie blokuje MVP)
- Biblioteka: `simplesoftwareio/simple-qrcode` (Laravel package)

### Krok 12: Stripe Integration
- Laravel Cashier
- Jeden plan na start: **$29/msc**
- Free trial: 14 dni
- Webhook handling: subscription created, cancelled, payment failed
- Middleware: po trial bez płatności → blokada wysyłki emaili

### Krok 13: Email Templates
- Domyślny template (działa od razu)
- Customizacja: zmiana tekstu, koloru przycisku, dodanie logo
- Trzymaj to proste — 1 template z edytowalnymi polami, nie "drag & drop builder"

---

## FAZA 3 — Tydzień 5-6: Landing Page + Deploy

### Krok 14: Landing Page (marketing site)
- Jedna strona, max 5 sekcji:
  1. Hero: "Get More 5-Star Google Reviews on Autopilot" + CTA "Start Free Trial"
  2. How it works (3 kroki z ikonkami)
  3. Pricing (jeden plan, prosty)
  4. Social proof (na start: "Trusted by X businesses" — nawet jeśli X=0, zamień na "Join early adopters")
  5. FAQ
- Zrób to w czystym HTML/Tailwind albo jako blade template
- **NIE** buduj osobnej apki na landing page

### Krok 15: Deploy
- CI/CD: GitHub Actions → deploy na Hetzner
- SSL: Certbot (darmowy)
- DNS: Cloudflare (darmowy)
- Backup: automatyczny daily backup DB

### Krok 16: Testy
- Feature testy na cały review flow (wysyłka → klik → rating → routing)
- Unit testy na logikę routingu (4-5 → Google, 1-3 → feedback)
- Smoke test: sam się zarejestruj i przejdź cały flow

---

## FAZA 4 — Miesiąc 2-3: Marketing + Iteracja

### Krok 17: Content SEO (najważniejsza rzecz po MVP)
Napisz artykuły targetujące long-tail keywords:
- "how to get more google reviews for cleaning business"
- "how to ask customers for reviews after service"
- "best way to collect google reviews for dentist"
- "review request email template for small business"
- "how to respond to negative google reviews"
- "google reviews vs yelp for local business"
- "how many google reviews do i need to rank higher"
- "automated review collection for restaurants"
- "how to get 5 star reviews on google"
- "review management for small business"

Cel: 2-3 artykuły tygodniowo, 1000-1500 słów każdy. Każdy z CTA do Twojego toola.

### Krok 18: Dystrybucja (poza SEO)
- Product Hunt launch (przygotuj grafiki, opis, 1-2 minutowe Loom demo)
- Reddit: r/smallbusiness, r/sweatystartup, r/EntrepreneurRideAlong
- Indie Hackers: build in public, dziel się postępami
- Twitter/X: pokaż co budujesz, taguj #buildinpublic

### Krok 19: Iteracja na feedbacku
- Co mówią pierwsi userzy?
- Które nisze konwertują najlepiej?
- Czy email wystarcza, czy potrzebują SMS? (jeśli tak → czas na LLC + Twilio)

---

## Budżet na 6 miesięcy (zrewidowany)

| Pozycja | Koszt |
|---|---|
| Domena (.com) | $12/rok |
| Hetzner VPS | €4.35/msc × 6 = ~€26 |
| Resend (email) | $0 (darmowy tier) |
| Stripe | $0 (prowizja od transakcji) |
| **RAZEM** | **~$40-50 (160-200 zł)** |

Jeśli walidacja się uda i chcesz dodać SMS (miesiąc 3-4):
- US LLC: ~$300-500
- Twilio: ~$10-30/msc
- Dodatkowy budżet: ~$400-600

---

## Metryki sukcesu

| Miesiąc | Cel |
|---|---|
| 1 | MVP live, sam testujesz flow |
| 2 | 5-10 rejestracji (free trial) |
| 3 | 3-5 płacących klientów ($87-145 MRR) |
| 4 | 10-15 płacących ($290-435 MRR) |
| 5 | 20+ płacących ($580+ MRR) |
| 6 | Break even + decyzja o LLC i SMS |

---

## Co robisz DZISIAJ

1. ✅ Kup domenę (30 min)
2. ✅ Postaw VPS na Hetzner (30 min)
3. ✅ Załóż repo, `laravel new`, pierwszy commit (15 min)
4. ✅ Załóż konto Stripe + Resend (15 min)
5. ✅ Zacznij od modeli: User, Tenant, BusinessProfile, ReviewRequest (reszta wieczoru)

**Nie planuj więcej. Buduj.**
