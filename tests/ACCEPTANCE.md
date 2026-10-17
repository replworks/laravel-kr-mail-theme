# Acceptance Criteria

One row per Acceptance Criterion (PRODUCT_SPEC.md). Each row maps the criterion to a
named passing test, or to a recorded manual run when no test can observe it.

Automated tests: `composer test`. Tests that need Mailpit skip when it is unreachable.

Recorded manual runs:

- `M1` — 2026-10-17, cable8mm, browser. SCR-001: changing EL-001, EL-002, EL-003, and
  EL-004 updates EL-005, EL-006, and EL-007; EL-003 Mobile shows EL-006 at 375 px;
  EL-004 Dark shows the Dark variant (FLOW-002 steps 1 and 2, T-023).
- `M2` — 2026-10-17, headless Chrome driving the served SCR-001 against Mailpit (T-024).
  EL-009 disabled for an empty and an invalid EL-008, enabled for a valid one; activating
  EL-009 with a valid address shows `delivered` in EL-010 and Mailpit receives one mail
  with an HTML part and a TEXT part; with Mailpit stopped EL-010 shows `failed: ERR-005`.
- `M3` — 2026-10-17, the check suite from the terminal (T-028). Exit code 0 when every
  combination passes; exit code 1 with a deliberately broken rule (the `List-Unsubscribe`
  header) and with Mailpit stopped, with the failing combination line and its FR ID.

| AC | Verifies | Named passing test or recorded manual run |
| --- | --- | --- |
| AC-001 | FR-001, FR-002 | `tests/Feature/ThemeInformationalTest.php` — "renders header, Mail Body, and footer in this order (FR-002, AC-001)" |
| AC-002 | FR-003, FR-004 | `tests/Feature/ThemeInformationalTest.php` — "shows the supplied Service Name and Logo (FR-003, FR-004, AC-002)" and "shows the Service Name and Logo placeholders when not supplied (FR-003, FR-004, AC-002)"; `tests/Unit/BrandInformationTest.php` — "gives the Service Name and Logo placeholders when not supplied (FR-003, FR-004)" |
| AC-003 | FR-005 to FR-008, ERR-002 | `tests/Feature/ThemeInformationalTest.php` — "shows only the supplied footer fields and never their labels otherwise (FR-005, FR-006, AC-003)" and "shows no footer field at all when none is supplied (FR-005, FR-006, AC-003)"; `tests/Feature/MailRendererTest.php` — "renders the TEXT part as Service Name, Mail Body, footer values, in this order (FR-034, FR-007, FR-005, FR-006, AC-003)" and "shows the Service Name placeholder and no footer field in the TEXT part when nothing is supplied (FR-007, FR-006, AC-003)"; `tests/Unit/BrandInformationTest.php` — "never fails when a Brand Information field is missing (FR-008, ERR-002)" |
| AC-004 | FR-011, FR-012, FR-023, FR-024, ERR-004 | `tests/Feature/MailRendererTest.php` — "ignores a Recipient User Identifier for Informational Mail (ERR-004, AC-004)" and "adds the List-Unsubscribe headers to Advertising Mail only (FR-021, FR-022, FR-023, FR-024, AC-008)"; `tests/Feature/ThemeInformationalTest.php` — "contains no Unsubscribe Link and no advertising slot (FR-009, FR-011, FR-012)" |
| AC-005 | FR-010, FR-013 | `tests/Feature/ThemeAdvertisingTest.php` — "renders the Advertising layout with header, body, footer, and the Unsubscribe Link (FR-010, FR-013, AC-005)" |
| AC-006 | ERR-001 | `tests/Feature/MailRendererTest.php` — "fails Advertising Mail without a Recipient User Identifier (ERR-001, AC-006)" |
| AC-007 | FR-016, FR-017, FR-018 | `tests/Feature/UnsubscribeUrlGeneratorTest.php` — "generates a signed, absolute Unsubscribe Link from the Recipient User Identifier (FR-016, FR-017)" and "uses the configured Unsubscribe Path (FR-018, AC-007)" |
| AC-008 | FR-021, FR-022 | `tests/Feature/MailRendererTest.php` — "adds the List-Unsubscribe headers to Advertising Mail only (FR-021, FR-022, FR-023, FR-024, AC-008)"; live header values: `tests/Checks/CheckSuiteTest.php` — "checks FR-054 to FR-058" (advertising combinations) |
| AC-009 | FR-025, FR-026, FR-027 | `tests/Unit/SubjectFormatterTest.php` — "leaves the Informational Mail subject unchanged (FR-026)", `prefixes the Advertising Mail subject with "(광고) " (FR-025)`, `does not add a second "(광고)" prefix (FR-027)`, "handles an empty subject (AC-009)" |
| AC-010 | FR-014, FR-015, FR-028, FR-029, SCR-001 | `tests/Feature/ThemeDarkMobileTest.php` — "provides a Light variant and a Dark variant (FR-028)" and "provides narrow-width CSS and keeps no fixed width wider than Mobile width (FR-029)"; recorded manual run `M1` (EL-003 and EL-004 selections keep the Unsubscribe Link visible and show no horizontal scrolling at Mobile width) |
| AC-011 | FR-030, FR-031, FR-032 | `tests/Unit/MarkdownHtmlRendererTest.php` — "converts a paragraph (FR-030, FR-032)", "converts a link (FR-032)", "converts a table (FR-032)", "converts a blockquote (FR-032)", "turns a link-only paragraph into a button (FR-031)"; `tests/MarkdownConversionTest.php` — "renders the sample to HTML with a table-based button for the link-only paragraph" |
| AC-012 | FR-033 to FR-037 | `tests/Feature/MailRendererTest.php` — "returns both an HTML part and a TEXT part (FR-033)", "produces a TEXT part with no HTML tags, no Markdown link syntax, and full URLs (FR-035, AC-012)", "puts the Unsubscribe Link in the TEXT part of Advertising Mail only (FR-036, FR-037)"; `tests/Unit/MarkdownTextRendererTest.php` — "keeps every URL in full, including the query string (FR-035)" |
| AC-013 | FR-038, FR-039, FR-040 | `tests/Feature/RendersKrThemeTest.php` — "sends a Mailable through the array mailer with Subject, HTML part, TEXT part, and headers (FR-038, AC-013)", "renders without copying any Package file into the Service (FR-038)", "uses Brand Information supplied through configuration (AC-013)"; `tests/Unit/BrandInformationTest.php` — "shows values supplied through environment variables alone (FR-039)"; `tests/Unit/ConfigurationTest.php` — "lets a Service config value win over an environment value and keeps other keys (FR-040)" |
| AC-014 | SCR-001, EL-009, FR-042 | `tests/Feature/WorkbenchSendTest.php` — "delivers the rendered mail to Mailpit with an HTML part and a TEXT part (AC-014, FR-041, FR-042)"; recorded manual run `M2` |
| AC-015 | ERR-005, SCR-001 | `tests/Feature/WorkbenchSendTest.php` — "shows failed with ERR-005 when Mailpit is unreachable and does not retry (AC-015, ERR-005)"; recorded manual run `M2` (Mailpit stopped) |
| AC-016 | FR-043, FR-045, FR-046, FR-047, FR-048, FR-049, FR-052 | `tests/Feature/UnsubscribeRequestTest.php` — "processes a valid GET immediately, without login, and shows SCR-002 Success (FR-043, FR-045, FR-046, FR-052, AC-016)" and "shows SCR-002 Success on GET even when the value is already false (FR-052, AC-016)"; `tests/Feature/ConsentRepositoryTest.php` — "creates the Consent Record when it does not exist and reports the change (FR-048)", "sets an existing true value to false and reports the change (FR-047)", "stores the marketing consent value as a boolean (FR-049)" |
| AC-017 | FR-044, FR-053 | `tests/Feature/UnsubscribeRequestTest.php` — "processes a valid POST with the one-click body and returns a success status (FR-044, FR-053, AC-017)" |
| AC-018 | FR-050, FR-051 | `tests/Feature/UnsubscribeRequestTest.php` — "fires the Unsubscribe Event once on the first valid request and not on a repeat (FR-050, FR-051, AC-018)" |
| AC-019 | FR-019, ERR-003 | `tests/Feature/UnsubscribeRequestTest.php` — "shows SCR-002 Error and changes nothing for an altered GET link (ERR-003, AC-019)", "shows SCR-002 Error for an unsigned GET link (ERR-003, AC-019)", "returns a non-success status and changes nothing for an altered POST link (ERR-003, AC-019)", "returns a non-success status for an unsigned POST link (ERR-003, AC-019)" |
| AC-020 | FR-054 to FR-059, ERR-007 | `tests/Checks/CheckSuiteTest.php` — "checks FR-054 to FR-058" (8 combinations, one line per combination); recorded manual run `M3` (exit code 0 only when every check passes) |
| AC-021 | FR-060, FR-061 | `tests/Feature/BodyViewRendererTest.php` — `turns a Recipient Name into "이름님" (FR-061, AC-021)`, `uses "회원님" for a null or empty Recipient Name (FR-061, AC-021)`, "makes a Service variable and $recipientDisplayName available in the view (FR-060, AC-021)" |
