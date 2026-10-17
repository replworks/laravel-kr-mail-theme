# Manual client smoke test

T-031. Layout check of the eight combinations in the target mail clients. Kakao webmail is a
Non-Goal and is not tested.

## Target clients

- Gmail web
- Gmail mobile app
- Naver Mail web
- Naver Mail mobile app
- Daum Mail
- Optional, observation only: a text-oriented device (for example an Apple Watch)

## Combinations

| # | Mail Kind | Sample Body |
| --- | --- | --- |
| 1 | Informational | receipt |
| 2 | Informational | button |
| 3 | Informational | long |
| 4 | Informational | quote |
| 5 | Advertising | receipt |
| 6 | Advertising | button |
| 7 | Advertising | long |
| 8 | Advertising | quote |

## How to send the mails

The Workbench delivers to Mailpit only (FR-042); "Delivering mail to real Recipients from the
Workbench" is a Non-Goal. Use one of these:

1. A scratch Laravel app that requires this Package (a path repository entry is enough before
   publication) and sends the eight combinations with your own mailer. This is the closest to
   how a Service sends.
2. Save each mail from Mailpit (`http://127.0.0.1:38025`) and forward it to the test accounts.
   Forwarding can change the HTML part; record that in Notes when you use this.

Record the Final Subject of each mail (Informational: the Mail Subject; Advertising:
`(광고) ` + the Mail Subject) so every mail is identifiable.

## Results

Legend: `OK` layout as intended; `note` described in Notes; `TODO` not observed yet.

| # | Combination | Gmail web | Gmail app | Naver web | Naver app | Daum | Text device | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | informational-receipt | TODO | TODO | TODO | TODO | TODO | TODO | |
| 2 | informational-button | TODO | TODO | TODO | TODO | TODO | TODO | |
| 3 | informational-long | TODO | TODO | TODO | TODO | TODO | TODO | |
| 4 | informational-quote | TODO | TODO | TODO | TODO | TODO | TODO | |
| 5 | advertising-receipt | TODO | TODO | TODO | TODO | TODO | TODO | |
| 6 | advertising-button | TODO | TODO | TODO | TODO | TODO | TODO | |
| 7 | advertising-long | TODO | TODO | TODO | TODO | TODO | TODO | |
| 8 | advertising-quote | TODO | TODO | TODO | TODO | TODO | TODO | |

Things to look at per mail: header and logo, Mail Body (paragraph, table, blockquote, button),
footer fields, the Unsubscribe Link in Advertising Mail, the `(광고) ` subject prefix, Light and
Dark, and Full width and Mobile width.

## Problems found

Each problem becomes a new task. New task IDs are proposed to the human before TASKS.md is
changed.

| # | Combination | Client | Problem | New task |
| --- | --- | --- | --- | --- |
| | | | | |

## Run record

- Date:
- Tester:
- Method (1 or 2):
- Client and app versions, OS:
