# TASKS.md

## Rules

- One task is one reviewable change. Each task has a Done when line that can be checked by running something.
- Tasks are done in ID order unless Depends says otherwise.
- A task that finds an [UNVERIFIED] item wrong updates PRODUCT_SPEC.md, TECH_STACK.md, or ARCHITECTURE.md in the same change.
- Status: `[ ]` open, `[x]` done.

## Phase 0: Verify the unknowns

- [x] T-001 Scaffold the repository
  - Depends: none
  - Work: composer.json (library, auto-discovery), Pest, Pint, Testbench with Workbench, `compose.yaml` for Mailpit, empty service provider.
  - Done when: `composer test` runs an empty suite with exit code 0, and the Mailpit web interface opens after `docker compose up -d`.

- [x] T-002 Verify version constraints and bundled libraries
  - Depends: T-001
  - Work: confirm the minimum PHP version of Laravel 12 and 13, the Testbench major for each, that league/commonmark (with table support) and tijsverkoyen/css-to-inline-styles come with illuminate/mail, and that a flat-key override in a Service config file keeps the other package keys. Fix `composer.json`.
  - Done when: `composer update` succeeds under both Laravel 12 and Laravel 13, and TECH_STACK.md no longer has these [UNVERIFIED] marks.

- [x] T-003 Verify the Mailpit API
  - Depends: T-001
  - Work: send one mail with HTML and TEXT parts over SMTP, then call the Mailpit HTTP API for message list, message HTML, message TEXT, headers, HTML check, and delete-all. Save one real HTML check response as a fixture.
  - Done when: ARCHITECTURE.md "Automated Checks" steps 3 and 4 name the real endpoints and the exact JSON field that means "feature with no support in any mail client".

- [x] T-004 Verify dark and mobile CSS against the HTML check
  - Depends: T-003
  - Work: build a minimal table-layout mail with `prefers-color-scheme` and a narrow-width media query in a `<style>` element. Run the HTML check.
  - Done when: the result is recorded. If the HTML check reports unsupported features, the Developer decides how FR-054 and FR-028 are reconciled and PRODUCT_SPEC.md is updated.

- [x] T-005 Verify raw HTML, TEXT, and headers on a Mailable
  - Depends: T-003
  - Work: in a Testbench test, build a Mailable that carries a raw HTML string, a raw TEXT string, and the two List-Unsubscribe headers, in Laravel 12 and 13. Send it to Mailpit.
  - Done when: Mailpit shows both parts and both headers, and the working calls are written in ARCHITECTURE.md under RendersKrTheme.

- [x] T-006 Verify Markdown conversion with one syntax tree
  - Depends: T-002
  - Work: with league/commonmark, render one Markdown sample to HTML with a custom rule for a link-only paragraph, and walk the same tree to produce TEXT. The sample has a table, a blockquote, a link, and a button paragraph.
  - Done when: the sample produces the expected HTML button and the expected TEXT forms, or the TEXT forms in ARCHITECTURE.md are corrected to what is achievable.

## Phase 1: Rendering core

- [x] T-007 Configuration and BrandInformation
  - Depends: T-002
  - Done when: unit tests pass for FR-003, FR-004, FR-007, FR-008, FR-039, FR-040, ERR-002: unset and empty values count as not supplied; Service Name and Logo give their Placeholders; the five footer fields are null when not supplied; environment-only values appear; a Service config value wins over an environment value; other keys survive the override (AC-002, AC-003, AC-013).

- [x] T-008 MailKind and SubjectFormatter
  - Depends: T-001
  - Done when: unit tests pass for FR-025, FR-026, FR-027 including a subject that already begins with `(광고)` (AC-009).

- [x] T-009 CssInliner
  - Depends: T-002
  - Work: inline `base.css`. Keep dark and mobile CSS as a `<style>` element in `<head>`. Support a forced scheme.
  - Done when: a unit test shows inlined styles on elements and the media-query CSS present and unchanged; with a forced scheme the variant appears without the media condition.

- [x] T-010 MarkdownConverter: HTML renderer
  - Depends: T-006
  - Done when: tests pass for FR-030, FR-031, FR-032: paragraph, link, table, blockquote, and the button rule; raw HTML in the Markdown is escaped (AC-011).

- [x] T-011 MarkdownConverter: TEXT renderer
  - Depends: T-006
  - Done when: tests pass for the Mail Body part of FR-035: no HTML tags, no Markdown link syntax, every URL in full.

- [x] T-012 Theme: Informational layout
  - Depends: T-007, T-009, T-010
  - Work: table layout, header (Service Name and Logo, or their Placeholders), Mail Body slot, footer that shows only the supplied fields without their labels when missing, Korean font stack.
  - Done when: tests pass for FR-001 to FR-006, FR-009, FR-011, FR-012 (AC-001, AC-002, AC-003).

- [x] T-013 Theme: dark variant and mobile CSS
  - Depends: T-012
  - Done when: a test confirms Light and Dark CSS exist (FR-028); a test confirms narrow-width CSS exists and no fixed width is wider than Mobile width (FR-029).

- [x] T-014 UnsubscribeUrlGenerator
  - Depends: T-001
  - Done when: tests pass for FR-016 to FR-020: absolute URL, uses the configured Unsubscribe Path, altered URL fails the signature check, URL does not expire (AC-007).

- [x] T-015 Theme: Advertising layout
  - Depends: T-012, T-014
  - Done when: tests pass for FR-010, FR-013 (AC-005), and an Advertising Mail with no Company Name and no Customer Center Contact renders with the Unsubscribe Link and neither field.

- [x] T-016 MailRenderer
  - Depends: T-008, T-011, T-013, T-015
  - Work: the pipeline from ARCHITECTURE.md, the TEXT layout, headers for Advertising Mail only.
  - Done when: tests pass for FR-005 to FR-007 in the TEXT part, FR-021 to FR-024, FR-033 to FR-037, ERR-001, ERR-004 (AC-003, AC-004, AC-006, AC-008, AC-012).

- [x] T-017 BodyViewRenderer
  - Depends: T-001
  - Done when: tests pass for FR-060 and FR-061: `홍길동` gives `홍길동님`; null and empty string give `회원님`; a Service variable and `$recipientDisplayName` are both available in the view; HTML in a Service variable is escaped (AC-021).

- [x] T-018 RendersKrTheme trait
  - Depends: T-005, T-016, T-017
  - Done when: a Testbench test sends a Mailable through the `array` mailer and asserts subject, HTML part, TEXT part, and headers; no Package file is copied into the test application (FR-038, AC-013).

## Phase 2: Unsubscribe

- [x] T-019 Migration and ConsentRepository
  - Depends: T-001
  - Done when: tests pass for FR-047 to FR-049 and FR-051 at repository level: row created when missing, true to false returns changed, false stays false and returns not changed, value stored as boolean.

- [x] T-020 Unsubscribed event
  - Depends: T-019
  - Done when: tests pass for the event payload: it carries the Recipient User Identifier of the Unsubscribe request. The FR-050 firing rule (once on the first change, not on a repeated request) and its AC-018 are asserted in T-021, where the controller fires the event.

- [x] T-021 Unsubscribe route, controller, and SCR-002
  - Depends: T-016, T-020
  - Done when: tests pass for FR-043 to FR-046, FR-050, FR-051, FR-052, FR-053, ERR-003 for both GET and POST, without login and without CSRF token (AC-016, AC-017, AC-018, AC-019).

## Phase 3: Workbench

- [x] T-022 Sample Bodies and Workbench Brand Information
  - Depends: T-016, T-017
  - Work: four Blade Sample Bodies that output Markdown, one fixed Brand Information set with every field, one fixed Recipient User Identifier, one fixed Recipient Name.
  - Done when: each of the four renders inside both Mail Kinds without error (FR-041).

- [x] T-023 SCR-001 preview
  - Depends: T-022
  - Work: EL-001 to EL-007 and the Initial, Loading, Populated, Error states.
  - Done when: in a browser, changing each selector updates EL-005, EL-006, EL-007 as listed in PRODUCT_SPEC Interactions; Mobile width is 375 px; Dark shows the Dark variant (FLOW-002 steps 1 and 2).

- [x] T-024 SCR-001 Test Delivery
  - Depends: T-023
  - Work: EL-008 to EL-011, Sending, Sent, Send failed states, SMTP to Mailpit only, both parts delivered.
  - Done when: AC-014 passes with Mailpit running; AC-015 passes with Mailpit stopped (EL-010 shows failed and ERR-005, no retry); EL-009 is disabled for an empty or invalid address (ERR-006).

## Phase 4: Automated checks

- [x] T-025 Mailpit client helper for tests
  - Depends: T-003, T-022
  - Done when: a test can empty Mailpit, send a rendered mail, and read back HTML part, TEXT part, headers, and HTML check result.

- [x] T-026 Check suite: FR assertions
  - Depends: T-025, T-016
  - Done when: the suite runs 8 combinations and asserts FR-054 to FR-057; each assertion message starts with its FR ID.

- [x] T-027 Check suite: snapshot comparison
  - Depends: T-026
  - Done when: FR-058 passes against approved `.html` and `.txt` files in `tests/Snapshots`; changing one character in a Theme file or the TEXT layout makes the matching combinations fail; the approve switch rewrites the files.

- [x] T-028 Check suite: exit behavior
  - Depends: T-027
  - Done when: AC-020 and ERR-007 are shown by breaking one rule on purpose: the output shows one fail line with the FR ID and the exit code is non-zero; restoring it gives exit code 0 (FR-059).

## Phase 5: Release

- [x] T-029 Acceptance pass
  - Depends: T-018, T-021, T-024, T-028
  - Done when: AC-001 to AC-021 each map to a named passing test or a recorded manual run, listed in one table in the repository.

- [x] T-030 README
  - Depends: T-029
  - Work: install; the environment variable list; what happens when a field is not set (Service Name and Logo show Placeholders that go out in real mail; footer fields are not shown, including Company Name and Customer Center Contact in Advertising Mail); Mail Body Markdown rules (link-only paragraph is a button, no four-space indentation inside Blade, `$recipientDisplayName`); Mailable usage; Advertising Mail usage; Unsubscribe Event listening; Workbench and checks usage; Non-Goals; what the Service still has to do itself (prior consent, sending hours, honoring the consent value). Include the legal basis (정보통신망법 제50조) and the date of the KISA guide the Theme was built against.
  - Done when: a person who has not seen the repository can complete FLOW-001 using only the README.

- [ ] T-031 Manual client smoke test
  - Depends: T-029
  - Work: send the 8 combinations to real accounts: Gmail web and app, Naver Mail web and app, Daum Mail. Also look at one mail on an Apple Watch or another text-oriented device if available (observation only). Record layout problems.
  - Done when: a short table of results is in the repository. Problems found become new tasks. Kakao webmail is not tested (Non-Goal).

- [ ] T-032 Publish v0.1.0
  - Depends: T-030, T-031
  - Work: choose Packagist or VCS install (TECH_STACK Open Decisions), tag, make the repository public.
  - Done when: `composer require` of the Package works in a clean Laravel 12 application and a clean Laravel 13 application.

- [ ] T-033 Adopt in wifinote.net
  - Depends: T-032
  - Work: require the Package with a pinned version, set Brand Information in `.env`, switch one Mailable to the trait with a Markdown Mail Body that uses the Recipient Display Name, add the Unsubscribed listener only if wifinote sends Advertising Mail.
  - Done when: wifinote.net sends one Informational Mail rendered by the Package in production, with an HTML part and a TEXT part and no layout code left in wifinote.
