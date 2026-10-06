<?php
/**
 * Rendered by SettingsController::render() with: $settings, $state,
 * $overview, $flash, $adminEmail, $cssPath, $action, $e, $labels.
 */
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="<?php echo $e($cssPath); ?>">
<script src="<?php echo $e($jsPath); ?>" defer></script>
</head>
<body>
<div class="crt-wrap">
    <div class="crt-head">
        <div class="crt-brand">
            <span class="crt-badge" aria-hidden="true">CR</span>
            <h1 class="crt-title">CookiesRegTech - Cookie Consent</h1>
        </div>
        <?php if ($state === 'connected'): ?><span class="crt-pill">● Connected</span><?php endif; ?>
    </div>
    <p class="crt-tagline">GDPR/CCPA consent banner, cookie scanner and consent log for this website.</p>

    <?php if ($flash): ?>
        <div class="crt-notice crt-notice-<?php echo $flash['type'] === 'ok' ? 'success' : ($flash['type'] === 'warn' ? 'warning' : 'error'); ?>"><?php echo $e($flash['message']); ?></div>
    <?php endif; ?>

    <?php if ($state === 'connect'): ?>
        <form method="post" action="<?php echo $action; ?>" class="crt-panel">
            <input type="hidden" name="crt_action" value="connect">
            <label class="crt-field">
                <span>Account email</span>
                <input type="email" name="email" required value="<?php echo $e($settings['email'] ?: $adminEmail); ?>">
                <small>The consent dashboard login. If this email already has a CookiesRegTech account, we send it a confirmation link instead of creating a new one.</small>
            </label>
            <label class="crt-consent">
                <input type="checkbox" name="consent" value="1" required>
                <span>Create a free CookiesRegTech account for this email, connect this website, and accept the
                    <a href="https://www.cookiesregtech.com/terms-and-condition" target="_blank" rel="noopener">Terms &amp; Conditions</a> and
                    <a href="https://www.cookiesregtech.com/privacy-policy" target="_blank" rel="noopener">Privacy Policy</a>.</span>
            </label>
            <p class="crt-actions">
                <button type="submit" class="crt-btn crt-btn-primary">Connect website</button>
                <span class="crt-hint">Free plan · no card required</span>
            </p>
        </form>


    <?php elseif ($state === 'pending'): ?>
        <div class="crt-notice-box">
            <h3>Almost done. Confirm from the inbox</h3>
            <p>A confirmation link was sent to <strong><?php echo $e($settings['email']); ?></strong> because this email already has a CookiesRegTech account. Click the link in that email, then come back here.</p>
        </div>
        <form method="post" action="<?php echo $action; ?>" class="crt-actions">
            <button type="submit" name="crt_action" value="check" class="crt-btn crt-btn-primary">Check status</button>
            <button type="submit" name="crt_action" value="reset" class="crt-btn">Start over</button>
        </form>

    <?php else: ?>
        <table class="crt-status">
            <tr><th scope="row">Account</th><td><?php echo $e($settings['email'] ?: ($overview['email'] ?? '—')); ?></td></tr>
            <tr><th scope="row">Website</th><td><?php echo $e($overview['hostname'] ?? $settings['domain_id']); ?></td></tr>
            <tr><th scope="row">Plan</th><td><?php echo $e($overview['plan']['name'] ?? '—'); ?></td></tr>
            <tr><th scope="row">Banner</th><td><?php
                if ($overview) {
                    echo !empty($overview['bannerPublished'])
                        ? 'Published (version ' . (int)$overview['bannerVersion'] . ')'
                        : 'Not published yet. Open the dashboard to publish it.';
                } else {
                    echo '—';
                }
            ?></td></tr>
            <?php if (!empty($overview['lastScan'])): ?>
                <tr><th scope="row">Last scan</th><td><?php echo $e(
                    date('F j, Y H:i', strtotime((string)$overview['lastScan']['completedAt'])) . ' UTC, '
                    . (int)$overview['lastScan']['urlsScanned'] . ' pages, '
                    . (int)$overview['lastScan']['cookiesFound'] . ' cookies found'
                ); ?></td></tr>
            <?php endif; ?>
        </table>

        <?php if (!empty($overview['cookies'])): ?>
            <details class="crt-panel" <?php echo count($overview['cookies']) <= 12 ? 'open' : ''; ?>>
                <summary>Cookies detected (<?php echo count($overview['cookies']); ?>)</summary>
                <table class="crt-cookies">
                    <tr><th>Cookie</th><th>Category</th><th>Domain</th><th>Duration</th></tr>
                    <?php foreach ($overview['cookies'] as $cookie): ?>
                        <tr>
                            <td><code><?php echo $e($cookie['name']); ?></code></td>
                            <td><?php echo $e($labels[$cookie['category']] ?? $cookie['category']); ?></td>
                            <td><?php echo $e($cookie['domain']); ?></td>
                            <td><?php echo $e($cookie['duration'] ?: 'Session'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p class="crt-help">Categories and descriptions are managed in the CookiesRegTech dashboard (Cookie Manager). Run a scan there to refresh this list.</p>
            </details>
        <?php elseif ($overview): ?>
            <p class="crt-help">No cookies recorded yet. Open the dashboard and run a scan from Cookie Manager to build the cookie list.</p>
        <?php endif; ?>

        <form method="post" action="<?php echo $action; ?>" class="crt-panel">
            <p>
                <label>
                    <input type="checkbox" name="banner_enabled" value="1" <?php echo $settings['banner_enabled'] ? 'checked' : ''; ?>>
                    Show the consent banner on this site
                </label>
            </p>
            <div class="crt-actions">
                <button type="submit" name="crt_action" value="save" class="crt-btn">Save</button>
            </div>
        </form>
        <div class="crt-actions">
            <button type="button" class="crt-btn crt-btn-primary" data-crt-open="dashboard" data-crt-endpoint="<?php echo $action; ?>">Open dashboard</button>
            <button type="button" class="crt-btn crt-btn-outline" data-crt-open="upgrade" data-crt-endpoint="<?php echo $action; ?>">Upgrade plan</button>
            <form method="post" action="<?php echo $action; ?>" style="display:inline" class="crt-inline"
                  data-crt-confirm="Disconnect this site? The banner turns off until you reconnect.">
                <input type="hidden" name="crt_action" value="disconnect">
                <button type="submit" class="crt-btn crt-btn-danger">Disconnect</button>
            </form>
        </div>
    <?php endif; ?>
    <p class="crt-version">CookiesRegTech TYPO3 extension v1.0.0</p>
</div>
</body>
</html>
