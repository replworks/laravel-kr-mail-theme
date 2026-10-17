# laravel-kr-mail-theme

A Laravel mail theme package for Korean-market services. It renders a Mail as an HTML part
and a TEXT part with a Korean-market layout, a Markdown Mail Body, a dark color scheme, a
narrow-viewport layout, and a one-click unsubscribe control for Advertising Mail.

Contents of a Rendered Mail come from the Service. The Package only renders; the Service
sends with its own mailer.

## Requirements

- PHP 8.3 or later
- Laravel 12 (`^12.0`) or Laravel 13 (`^13.0`)

## Install

```sh
composer require replworks/laravel-kr-mail-theme
php artisan migrate
```

The service provider is discovered automatically. Migrations create the `kr_mail_consents`
table (Consent Records).

Set `APP_URL` to the host Recipients reach from their mail client. The Unsubscribe Link is
absolute, so a wrong `APP_URL` produces a link that does not work.

## Brand Information

Set Brand Information through environment variables or through one Service configuration
file. A value in the configuration file wins over the environment variable.

| Field | Environment variable |
| --- | --- |
| Service Name | `KR_MAIL_SERVICE_NAME` |
| Logo | `KR_MAIL_LOGO_URL` |
| Company Name | `KR_MAIL_COMPANY_NAME` |
| Customer Center Contact | `KR_MAIL_CUSTOMER_CENTER` |
| Business Registration Number | `KR_MAIL_BUSINESS_REGISTRATION_NUMBER` |
| Address | `KR_MAIL_ADDRESS` |
| Terms Link | `KR_MAIL_TERMS_URL` |
| Unsubscribe Path | `KR_MAIL_UNSUBSCRIBE_PATH` (default `unsubscribe`) |

For the configuration file, create `config/kr-mail-theme.php` in the Service with the same
flat top-level keys:

```php
<?php

return [
    'service_name' => '위피노트',
    'company_name' => '(주)위피컴퍼니',
    'customer_center' => '1588-0000',
    'business_registration_number' => '123-45-67890',
    'address' => '서울특별시 강남구 테헤란로 1',
    'terms_url' => 'https://example.com/terms',
];
```

When a field is not set:

- Service Name shows `[서비스명]` and Logo shows `[로고]`. These placeholders go out in real
  mail; the Package does not warn or fail.
- The footer fields (Company Name, Customer Center Contact, Business Registration Number,
  Address, Terms Link) are not shown at all, and no empty label is shown for them. Advertising
  Mail with no Company Name or Customer Center Contact is still sent with neither.

## Sending Mail

Use the `RendersKrTheme` trait on a Mailable and call `krTheme()` from `build()`.

```php
<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\RendersKrTheme;

class OrderShipped extends Mailable
{
    use RendersKrTheme;

    public function __construct(public Order $order) {}

    public function build(): static
    {
        return $this->krTheme(
            MailKind::Informational,
            '주문이 완료되었습니다',
            'mail.bodies.order-shipped',
            ['order' => $this->order],
            recipientName: $this->order->user->name,
        );
    }
}
```

`krTheme(kind, subject, bodyView, data = [], userIdentifier = null, recipientName = null)`
renders `bodyView`, converts the Markdown Mail Body to the HTML part and the TEXT part, and
sets the Final Subject, both parts, and the headers. Do not call `subject()`, `html()`, or
`text()` after it.

Do not define `headers()` in a Mailable that uses the trait: the trait supplies it for the
List-Unsubscribe headers of Advertising Mail.

The Mail Body is a Blade view that outputs Markdown:

```blade
{{ $recipientDisplayName }}, 안녕하세요.

{{ $order->number }} 주문이 완료되었습니다.

[주문 확인하기]({{ $order->url }})
```

Mail Body rules:

- Supported at minimum: paragraph, link, table, blockquote. Headings, lists, and emphasis are
  converted if the Markdown library supports them; they are not covered by the automated
  checks.
- A paragraph that consists of only one link becomes a button in the HTML part. In the TEXT
  part it becomes `label: URL`.
- Raw HTML in the Mail Body is escaped, and unsafe link schemes are not rendered as links.
- The Recipient Display Name is available as `$recipientDisplayName`: `홍길동님` when a
  Recipient Name is supplied, and `회원님` when it is not.
- Do not indent Mail Body lines with four spaces or a tab inside Blade. Indented lines become
  a Markdown code block, which the theme renders as preformatted text.
- Every Mail Body URL appears in full in the TEXT part.

## Advertising Mail

Advertising Mail is the same call with `MailKind::Advertising` and a Recipient User
Identifier:

```php
return $this->krTheme(
    MailKind::Advertising,
    '신규 이벤트 안내',
    'mail.bodies.event',
    ['event' => $this->event],
    $user->getKey(),
    $user->name,
);
```

- The Recipient User Identifier is required. Without it, rendering fails and the error names
  the Recipient User Identifier.
- The Final Subject begins with `(광고) ` followed by the Mail Subject. An existing `(광고)`
  prefix is not doubled.
- The footer shows the Unsubscribe Link, and the mail carries
  `List-Unsubscribe: <Unsubscribe Link>` and `List-Unsubscribe-Post: List-Unsubscribe=One-Click`,
  so Gmail and Apple Mail show an unsubscribe control.
- The TEXT part contains the Unsubscribe Link.

The Package does not check the marketing consent value before rendering or sending. Send
Advertising Mail only to Recipients with prior consent (정보통신망법 제50조), and honor the
marketing consent value yourself (see Non-Goals).

## Unsubscribe

The Package registers a route `GET|POST {unsubscribe_path}` named
`kr-mail-theme.unsubscribe`. It is registered outside the `web` middleware group: no login is
required and no CSRF token is needed, so a mail client can POST the one-click body. A valid
request sets the marketing consent value of the Recipient's Consent Record to `false` and
shows SCR-002. The Unsubscribe Link is signed and does not expire.

The Package stores the marketing consent value in `kr_mail_consents` as a boolean. It only
ever writes `false`; it never restores `true`.

The Package fires `Replworks\KrMailTheme\Unsubscribe\Unsubscribed` once per request that
changes the value from `true` (or from no Consent Record) to `false`. It does not fire when
the value is already `false`. Listen to it to update your own records:

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Replworks\KrMailTheme\Unsubscribe\Unsubscribed;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(function (Unsubscribed $event) {
            \App\Models\User::whereKey($event->userIdentifier)
                ->update(['marketing_consent' => false]);
        });
    }
}
```

Use the same value for the Recipient User Identifier in `krTheme()` and in your own records;
the Unsubscribe Link and the event both carry it.

## What the Service still does

The Package does not:

- collect prior consent for Advertising Mail,
- restrict sending hours,
- honor the marketing consent value when sending,
- block or filter Rendered Mail by the marketing consent value,
- send mail.

Do these in the Service.

## Workbench (repository only)

The Workbench is a Testbench application in this repository. It needs Docker for the Mailpit
container.

```sh
docker compose up -d
vendor/bin/testbench serve --host=127.0.0.1 --port=8099
```

Open `http://127.0.0.1:8099/` (SCR-001):

- EL-001 to EL-004: Mail Kind, Sample Body, viewport (Full width or Mobile width, 375 px), and
  color scheme (Light or Dark). They update EL-005 (Final Subject), EL-006 (HTML preview), and
  EL-007 (TEXT preview).
- EL-008 and EL-009: type a Recipient Address and Send Test. EL-010 shows in-progress,
  delivered, or failed with the ERR ID. EL-009 is disabled for an empty or invalid address.
- EL-011: open the Mailpit web interface (`http://127.0.0.1:38025`).

Routes: `GET /` (SCR-001), `GET /preview`, `GET /meta`, `POST /send`. Test Delivery uses SMTP
to the local Mailpit only.

## Automated checks

```sh
vendor/bin/pest tests/Checks   # the checks; one line per combination
vendor/bin/pest                # the whole suite (unit, feature, checks)
```

- Eight combinations (2 Mail Kinds x 4 Sample Bodies) render with the Workbench Brand
  Information, deliver to Mailpit, and assert FR-054 to FR-058.
- Every assertion message starts with its FR ID, and each combination prints one line.
- Exit code is 0 only when every combination passes.
- Approved parts are in `tests/Snapshots/<kind>-<body>.html` and `.txt`. After an intentional
  change, approve new files with
  `KR_MAIL_THEME_APPROVE_SNAPSHOTS=1 vendor/bin/pest tests/Checks` and review the Git diff.
- Mailpit must be running (`docker compose up -d`). Without it every combination fails with a
  Mailpit unreachable message.
- The Acceptance Criteria mapping is in `tests/ACCEPTANCE.md`.

## Non-Goals

- More than one Theme, or a way to select a Theme.
- Mail Body templates for particular purposes. The Service writes its own Mail Bodies.
- Copying a Theme into a Service.
- Terms-change notification mails.
- Restoring a marketing consent value to `true` after an unsubscribe.
- Collecting prior consent, restricting sending hours, or blocking delivery.
- Warning or failing when a Placeholder is used, or when an optional footer field is not
  supplied.
- A greeting in the layout. The Mail Body wording uses the Recipient Display Name.
- Calendar or event data for mail clients.
- Kakao webmail in the automated checks.

## Legal basis

Advertising Mail in Korea needs an unsubscribe method and a `(광고)` label:
정보통신망법 제50조. This theme implements the `(광고)` subject prefix, the footer Unsubscribe
Link, and the List-Unsubscribe headers.

Built against the KISA guide [KISA guide title and date placeholder]. Replace this placeholder
with the guide title and date the theme was built against.
