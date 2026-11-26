# KOS Cookie Consent Package

Cookie consent package for Concrete CMS with multilingual popup copy, per-site cookie categories, consent logs/export, and gated tracking-code injection.

For a shorter Thai summary for PM/client handoff, see [PACKAGE_DETAILS.md](PACKAGE_DETAILS.md).  
For a deeper engineering map, see [CONTEXT.md](CONTEXT.md).

## Current Scope

- Frontend cookie banner and preference modal are rendered automatically by the package.
- Cookie categories, copy, logs, and export are managed from Dashboard single pages.
- Visitors can use `Accept All`, `Reject All`, or `Save Preferences`.
- Optional cookies default to `false` until the visitor explicitly opts in.
- Tracking code is injected only for consented categories.
- Saved visitors get a `Cookie Settings` entry point to reopen the modal later.
- Cookie categories can define first-party cookies to clear when consent is withdrawn.

## Consent Model

The package now uses a first-party browser identifier, not visitor IP, as the main consent key.

- Primary identity: `kos_cookie_consent_id`
- Scope: per browser/profile, per site, per consent version
- Server-side lookup key: `consent_id + site_id + consent_version`
- Audit-only field: visitor IP is still stored in logs, but it is not used to determine consent state

What this means in practice:

- Same browser profile keeps the same consent state.
- Different browsers, incognito windows, or devices get separate consent states.
- Deleting `kos_cookie_consent_id` makes that browser look like a new visitor.
- Changing cookie definitions or popup copy changes `consent_version`, so the visitor is prompted again.

## Visitor Behavior

### First Visit

- Frontend JS creates or reuses `kos_cookie_consent_id`.
- JS calls `GET /kos-cookie-consent/state`.
- If no saved state exists for the current `consent_id + site_id + consent_version`, the banner is shown.

### Accept All

- All optional toggles are turned on.
- Consent is saved.
- Banner is hidden.
- `Cookie Settings` remains available for later changes.

### Reject All

- All optional toggles are turned off.
- Consent is still saved as an answered state.
- Banner is hidden and should not reappear until consent version changes or the browser gets a new `consent_id`.

### Save Preferences

- Only the currently selected optional toggles are saved.
- Saving with everything off is valid and counts as an answered state.
- Withdrawing previously granted categories clears the configured first-party cookies for those categories and reloads the page so the next execution pass respects the updated consent state.

### Closing The Modal

- Closing the modal is not treated as consent.
- If the visitor has not answered yet, the banner remains the first layer.
- If consent is already saved and the modal was reopened from `Cookie Settings`, closing the modal only closes the modal.

## Browser Cookies

The package uses these browser cookies:

- `kos_cookie_consent_id`: first-party random identifier used as the consent key
- `consent`: marker cookie set after a successful save
- `consent_version`: browser-side marker of the last saved consent version

Important:

- Consent-cookie retention is configurable from `/dashboard/cookie_consent/setting`.
- Default retention is `30 days`.
- Allowed retention is `1-400 days` so dashboard input stays within modern browser persistence caps.
- These cookies are written as first-party cookies with `Path=/`, `SameSite=Lax`, and `Secure` when the site is served over HTTPS.
- The source of truth is still the database log keyed by `consent_id + site_id + consent_version`.
- There is no separate server-side consent-expiry rule yet beyond consent-version changes; the configurable retention applies to browser cookies only.

## Managed Cookie Cleanup

Each optional cookie category can define first-party cookies that should be removed when a visitor withdraws consent.

- Configure this on each cookie category in `/dashboard/cookie_consent/setting`.
- Enter one cookie name per line.
- Use `*` only at the end for prefix matches, for example `_ga_*`.
- Cookie path defaults to `/`.
- Domain override is optional and should be a hostname only.
- The cookie names field has an **Examples by tool** picker (Meta Pixel, TikTok Pixel, LINE Tag, Google Ads, Google Analytics, Hotjar, Microsoft Clarity). Pick a tool, then use **Add to Cookie names** or **Copy**.

Usual cookie names per tool (tools can change them, so check the cookies your site really sets in browser DevTools > Application > Cookies):

| Tool | Category | Cookie names |
|---|---|---|
| Meta Pixel | Marketing | `_fbp`, `_fbc` |
| TikTok Pixel | Marketing | `_ttp`, `_tt_*` |
| LINE Tag | Marketing | `__lt__*` |
| Google Ads | Marketing | `_gcl_*`, `_gac_*` |
| Google Analytics | Statistics | `_ga`, `_ga_*`, `_gid` |
| Hotjar | Statistics | `_hj*` |
| Microsoft Clarity | Statistics | `_clck`, `_clsk` |

Important limitations:

- This cleanup is for first-party cookies on your own site domain.
- Browser rules do not allow this package to delete third-party cookies stored on another vendor domain.
- `HttpOnly` cookies cannot be removed from frontend JavaScript.
- Do not add `kos_cookie_consent_id`, `consent`, or `consent_version` to cleanup rules because they are required to remember the visitor choice.

## Database Model

Core tables:

- `_KosCookie`: cookie categories and tracking snippets
- `_KosCookieContent`: translated cookie names and descriptions
- `_KosIP`: legacy table name, now used for consent identity rows
- `_KosLog`: consent log rows
- `_KosCookiePopupCopy`: multilingual popup copy per site/language

Important details:

- `_KosIP.consent_id` stores the browser-level consent identity.
- `_KosIP.IP` stores visitor IP for audit/history only.
- `_KosLog` stores one row per selected cookie ID.
- If a visitor saves with no optional cookie selected, the package stores a sentinel `cid = 0` row so `Reject All` and all-off preferences still count as saved consent.

## Dashboard Pages

- `/dashboard/cookie_consent`
- `/dashboard/cookie_consent/logs`
- `/dashboard/cookie_consent/setting`
- `/dashboard/cookie_consent/popup_content`
- `/dashboard/cookie_consent/export`

### Logs / Export

Logs and CSV export currently show:

- `Consent ID`
- `IP Address`
- per-cookie YES/NO columns
- `Consent Version`
- `Created` timestamp

Timestamps are stored in UTC and displayed/exported in ICT through the existing helper methods.

## Popup Copy Fields

Popup copy is stored in `_KosCookiePopupCopy` and managed per site-language row.

Current fields:

- `banner_title`
- `banner_summary`
- `modal_title`
- `modal_intro_one`
- `modal_intro_two`
- `preferences_title`
- `save_label`
- `accept_label`
- `decline_label`
- `settings_label`

Behavior notes:

- `modal_intro_two` is optional. If left blank, the second paragraph is not rendered on the frontend.
- `settings_label` is reused for both the floating `Cookie Settings` trigger and the banner `Customize` action label.

## Frontend UI Notes

Stable BEM-style classes live under `kos-cookie-popup__*`.

Useful button classes for overrides include:

- `kos-cookie-popup__button--customize`
- `kos-cookie-popup__button--reject`
- `kos-cookie-popup__button--accept`
- `kos-cookie-popup__button--save`
- `kos-cookie-popup__button--banner-customize`
- `kos-cookie-popup__button--banner-reject`
- `kos-cookie-popup__button--banner-accept`
- `kos-cookie-popup__button--modal-reject`
- `kos-cookie-popup__button--modal-save`
- `kos-cookie-popup__button--modal-accept`

## Routes

Legacy-compatible routes:

- `POST /cookieConsentSave`
- `POST /cookieConsentSaveV2`
- `GET /cookieConsentStateV2`

Current frontend routes:

- `POST /kos-cookie-consent/save`
- `GET /kos-cookie-consent/state`

The `state` response is sent with `no-store` headers to reduce cache-related consent bugs.

## Upgrade Notes

- Current package version: `0.2.9`
- Run package upgrade after deploying package code changes.
- Clear Concrete CMS cache after upgrade.
- Existing legacy IP-based rows can be adopted into the new `consent_id` model on first fresh lookup, but new saves should always use `consent_id`.

## Developer Notes

- Keep `_Kos*` table names for backwards compatibility.
- Do not change route paths unless you update both PHP and JS consumers.
- Keep `kos-cookie-popup__*` class names stable unless you also update theme overrides.
- Optional cookies should remain default-off unless product/legal requirements explicitly change.
- Managed cookie cleanup rules should only target first-party cookies that belong to the same consent category.
- If you add popup copy fields, update:
  - table creation/migration in `controller.php`
  - dashboard form/controller
  - service read/write mapping
  - frontend rendering
  - consent version expectations

## Quick Validation Checklist

- Banner appears for a new browser/profile.
- `Reject All` hides the banner and keeps `saved: true` on later page loads.
- `Accept All` restores saved toggles on reload.
- `Save Preferences` persists mixed selections correctly.
- `Cookie Settings` reopens the modal after consent is saved.
- Changing popup copy or cookie definitions produces a new consent version and re-prompts visitors.
- Changing cookie retention updates the consent-cookie max-age used by later state/save responses.
- Withdrawing previously granted consent clears the configured first-party cookies for the revoked categories.
- Logs and export show `Consent ID`, `IP Address`, and consent version correctly.

## Google Consent Mode V2

Requires concrete5 8.5.7+ and PHP 7.4+.

1. Upgrade the package, then open **Dashboard > Cookie Consent > Setting** and enable *Consent Mode V2*.
2. Edit each cookie category and tick the signals it controls (for Google: `analytics_storage` for analytics, `ad_storage` + `ad_user_data` + `ad_personalization` for ads).
3. Enter your Google tag ID in the Settings card (see *Google tag* below).
4. Verify in the browser: `dataLayer` shows `consent default` (denied) first, then `consent update` after the visitor chooses.

### Google tag (one tag for the whole site)

Google Analytics and Google Ads share **one** Google tag. The categories do not load their own copy; they only decide which Consent Mode signals are granted (Necessary: `functionality_storage` + `security_storage`, Statistics: `analytics_storage`, Preferences: `personalization_storage`, Marketing: the three `ad_*` signals).

1. Dashboard > Cookie Consent > Settings > *Google Consent Mode V2*, turn it on.
2. Enter the **Google tag ID** (`G-XXXXXXXXXX` for Analytics, `AW-XXXXXXXXXX` for Ads, `GTM-XXXXXXX` for Tag Manager; several IDs separated by commas) and save.
3. The package prints the loader on every page right after the consent default, so the tag always reads the visitor choice first. The field works only while Consent Mode V2 is on.

Notes:
- Use category *Tracking codes* only for tools that are not Google (Meta Pixel, Hotjar, ...). Those load after the visitor accepts the category.
- If a category's code already contains a Google tag script while an ID is set, the Settings page shows a warning (the tag would load twice). Remove one of them.
- The GTM `noscript` iframe is not printed (the banner needs JavaScript anyway).
- Do **not** put tracking scripts in Concrete's own *Dashboard > System & Settings > SEO & Statistics > Tracking Codes*. They load for every visitor immediately and skip consent.

#### Using Google Tag Manager

Enter the container ID (`GTM-XXXXXXX`, copied from the GTM workspace) in the same **Google tag ID** field. There is no separate GTM field.

1. Settings > *Google Consent Mode V2*: turn it on, enter `GTM-XXXXXXX`, save.
2. In GTM, create the Google tag (GA4) with your Measurement ID `G-XXXXXXXXXX` and a *Initialization - All Pages* trigger. Do not enter the same `G-` ID in the package field, or visits are counted twice.
3. Open **Admin > Container Settings > Enable consent overview** and check that your tags show the consent they need. Google tags read the signals by themselves.
4. For tags from other vendors, follow the next section (`cookie_consent_update` event).
5. Test with Tag Assistant: the *Consent* tab must show the default state first, then the update after the visitor chooses.

If the container is also used outside this package, remove any GTM snippet pasted in the theme or in a category so it does not load twice.

### Google Tag Manager: tags without built-in consent support

Google tags (GA4, Google Ads, Conversion Linker, Google Ads Remarketing) read the Consent Mode signals by themselves and need no extra trigger. Tags from other vendors (Meta Pixel, Microsoft Advertising, LinkedIn Insight, TikTok, custom HTML) do not, so they must be held back until the visitor has consented.

On every page load with a saved choice, and again right after the visitor saves a choice, the package pushes this to `dataLayer`:

```js
{
  event: 'cookie_consent_update',
  cookie_consent_saved: true,            // false when no choice has been saved yet
  cookie_consent_state: {                // granted / denied per signal
    ad_storage: 'granted', analytics_storage: 'denied',
    ad_user_data: 'granted', ad_personalization: 'granted',
    functionality_storage: 'granted', personalization_storage: 'denied', security_storage: 'granted'
  }
}
```

Setup in GTM:

1. **Triggers > New** > *Custom Event*. Name it `Cookie Consent Update` and set the event name to `cookie_consent_update`. The event name must match exactly and is all lower case.
2. Open each non-Google tag. Under **Advanced Settings > Consent Settings**, choose *Require additional consent for tag to fire* and add the signals the tag needs. For advertising tags use `ad_storage`, `ad_user_data` and `ad_personalization`.
3. Under **Triggering**, remove the *All Pages* / *Initialization* trigger and add the `Cookie Consent Update` trigger. The tag then fires only after consent has been granted.
4. Use **Admin > Container Settings > Enable consent overview**, then the shield icon on the Tags page, to find tags listed under *Consent Not Configured*. Tags that have no value in the *Built-in Consent* column are the ones that need steps 2 and 3.
5. Whenever a new tag is added to the container, check its consent settings again.

If the site loads `gtag.js` directly without GTM, or only uses Google tags, this trigger is not needed.

### Testing Consent Mode V2

Use GTM Preview (Tag Assistant) or the browser DevTools console on a private window.

| Step | Expected result |
| --- | --- |
| Open the site before choosing anything | `dataLayer` contains `consent default` with every signal `denied` as the first consent entry. The GA4 page view has `gcs=G100`. Non-Google tags with the `Cookie Consent Update` trigger have not fired. |
| Click *Accept All*, then open another page | `consent update` is pushed and `cookie_consent_update` appears in the Tag Assistant summary. The GA4 page view has `gcs=G111`. The non-Google tags have now fired. |
| Click *Reject All*, then reload | Signals stay `denied`, `gcs` stays `G100`, and non-Google tags do not fire. |
| Choose *Save Preferences* with only Statistics on | `analytics_storage` is `granted`, the `ad_*` signals stay `denied`, and `gcs` shows `G101`. |
| Reload after any saved choice | The saved state is restored from the `kos_consent_mode` cookie before GTM loads, so there is no `G100` flash. |

In the console, `dataLayer.filter(e => e[0] === 'consent')` lists the consent commands in the order they were sent.
