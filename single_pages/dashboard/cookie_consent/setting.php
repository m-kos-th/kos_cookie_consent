<?php defined('C5_EXECUTE') or die('Access denied.'); ?>

<div class="ccm-dashboard-header-buttons">
  <?php if (empty($_type)) { ?>
    <div class="kos-actions">
      <form method="post" action="<?php echo $view->action('add_default_categories'); ?>" class="kos-actions__form">
        <input type="hidden" name="token" value="<?php echo $_tokenDefaults; ?>">
        <button type="submit" class="btn btn-secondary kos-actions__btn"><?php echo t('Add default categories'); ?></button>
      </form>
      <a class="btn btn-primary kos-actions__btn" href="<?php echo $controller->kosdesign->getLink('add'); ?>"><?php echo t('Add category'); ?></a>
    </div>
  <?php } ?>
</div>
<div class="setting">
  <?php if (isset($_type) && ($_type === 'add' || $_type === 'edit')) { ?>
    <div class="form">
      <?php
      $editor = Core::make('editor');
      $editor->setAllowFileManager(false);
      ?>
      <form method="post" action="<?php echo $view->action('save', isset($id) ? $id : null); ?>">
        <fieldset>
          <legend><?php echo t('Category name') ?></legend>
          <p class="kos-hint"><?php echo t('Shown only in this dashboard. Visitors see the names you enter per language below.'); ?></p>
          <div class="form-group">
            <?php echo $form->text('name', isset($_post['name']) ? $_post['name'] : '', ['required' => 'required']) ?>
          </div>
          <?php
            $_optionSite = $controller->kosdesign->getSiteLanguage();
            if (!empty($_optionSite)) {
              $_option = ['' => t('Please select site')];

              foreach ($_optionSite as $_val) {
                $_option[$_val['site_id']] = $_val['site_name'];
              }
          ?>
          <legend><?php echo t('Site') ?></legend>
          <div class="form-group">
            <?php echo $form->select('site_id', $_option, isset($_post['site_id']) ? $_post['site_id'] : null, ['required' => 'required']);?>
          </div>
          <?php } ?>
          <legend><?php echo t('Status') ?></legend>
          <div class="form-group">
            <div class="form-check">
              <?php echo $form->checkbox('active', '1', isset($_post['active']) ? $_post['active'] : '0') ?>
              <label class="form-check-label" for="active"><?php echo t('Show this category on the website') ?></label>
            </div>
            <div class="form-check">
              <?php echo $form->checkbox('always', '1', isset($_post['always']) ? $_post['always'] : '1') ?>
              <label class="form-check-label" for="always"><?php echo t('Always active (visitors cannot turn it off)') ?></label>
            </div>
          </div>
        </fieldset>
        <hr>
        <fieldset>
          <legend><?php echo t('Text shown to visitors') ?></legend>
          <?php
            $_options = $controller->kosdesign->getSiteLanguage();
            $_bySite = [];
            foreach ($_options as $_val) {
              $_bySite[$_val['site_id']][] = $_val;
            }
            foreach ($_bySite as $_siteid => $_langs) {
              $_multi = count($_langs) > 1;
          ?>
          <div class="kos-lang d-none" data-site="<?php echo (int) $_siteid; ?>" style="display:none">
            <div class="kos-lang__help">
              <?php if ($_multi) { ?>
                <?php echo t('Fill in a name for each language. Visitors see their own language.'); ?>
              <?php } else { ?>
                <?php echo t('Enter the name and description visitors will see.'); ?>
              <?php } ?>
            </div>
            <?php if ($_multi) { ?>
              <ul class="kos-lang__tabs" role="tablist">
                <?php foreach ($_langs as $_i => $_val) { ?>
                  <li>
                    <button type="button" class="kos-lang__tab<?php echo $_i === 0 ? ' is-active' : ''; ?>" role="tab" data-lang-target="<?php echo h($_siteid . '-' . $_val['language']); ?>">
                      <?php echo h($_val['text']); ?> <small class="text-muted">(<?php echo h($_val['language']); ?>)</small>
                      <span class="kos-lang__status is-missing"><?php echo t('Required'); ?></span>
                    </button>
                  </li>
                <?php } ?>
              </ul>
            <?php } ?>
            <?php foreach ($_langs as $_i => $_val) {
              $_code = $_val['language'];
              $_name = 'label[' . $_siteid . '][' . $_code . ']';
              $_content = 'content[' . $_siteid . '][' . $_code . ']';
            ?>
              <div class="kos-lang__pane<?php echo $_i === 0 ? ' is-active' : ''; ?>" data-lang-pane="<?php echo h($_siteid . '-' . $_code); ?>">
                <?php if ($_multi) { ?>
                  <p class="kos-lang__pane-title"><?php echo h($_val['text']); ?> (<?php echo h($_code); ?>)</p>
                <?php } ?>
                <div class="kos-lang__error"><?php echo t('Please enter a name for this language.'); ?></div>
                <div class="form-group">
                  <?php echo $form->label($_name, t('Name') . ($_multi ? ' (' . h($_val['text']) . ')' : '')) ?>
                  <?php echo $form->text($_name, isset($_post['label'][$_siteid][$_code]) ? $_post['label'][$_siteid][$_code] : null, ['class' => 'kos-lang__name']) ?>
                </div>
                <div class="form-group">
                  <?php echo $form->label($_content, t('Description') . ($_multi ? ' (' . h($_val['text']) . ')' : '')) ?>
                  <?php echo $editor->outputStandardEditor($_content, isset($_post['content'][$_siteid][$_code]) ? $_post['content'][$_siteid][$_code] : null); ?>
                </div>
              </div>
            <?php } ?>
          </div>
          <?php } ?>
          <?php
            $_postedModeTypes = isset($_post['consent_mode_types']) ? $_post['consent_mode_types'] : [];
            $_selectedModeTypes = $controller->kosdesign->parseConsentModeTypes($_postedModeTypes);
            $_signalLabels = [
              'ad_storage' => t('Store advertising cookies'),
              'analytics_storage' => t('Store analytics cookies (visit statistics)'),
              'ad_user_data' => t('Send visitor data to Google for ads'),
              'ad_personalization' => t('Show personalized ads'),
              'functionality_storage' => t('Store site features (e.g. language choice)'),
              'personalization_storage' => t('Store personalization (e.g. recommendations)'),
              'security_storage' => t('Store security and fraud-prevention data'),
            ];
            $_hasCodes = !empty($_post['code_header']) || !empty($_post['code_body']) || !empty($_post['code_footer']);
            $_hasCleanup = !empty($_post['managed_cookie_names']) || !empty($_post['managed_cookie_domain']);
          ?>
          <div class="kos-section">
            <h3 class="kos-section__title"><?php echo t('Google Consent Mode V2') ?></h3>
            <p class="kos-hint"><?php echo t('What Google may do once a visitor accepts this category. Used only when Consent Mode is turned on.'); ?></p>
            <p class="kos-hint">
              <?php echo t('Not sure which signals belong to this category? Read:'); ?>
              <a class="kos-hint__link" href="https://support.google.com/analytics/answer/12334711" target="_blank" rel="noopener noreferrer"><?php echo t('Consent types explained'); ?></a>
              &middot;
              <a class="kos-hint__link" href="https://developers.google.com/tag-platform/security/guides/consent" target="_blank" rel="noopener noreferrer"><?php echo t('Set up consent mode on websites'); ?></a>
            </p>
            <?php foreach ((isset($_consentModeSignals) ? $_consentModeSignals : []) as $_signal) { ?>
              <div class="form-check kos-signal">
                <input type="checkbox" class="form-check-input" id="consent_mode_types_<?php echo h($_signal); ?>" name="consent_mode_types[]" value="<?php echo h($_signal); ?>"<?php echo in_array($_signal, $_selectedModeTypes, true) ? ' checked' : ''; ?>>
                <label class="form-check-label" for="consent_mode_types_<?php echo h($_signal); ?>">
                  <code><?php echo h($_signal); ?></code>
                  <?php if (isset($_signalLabels[$_signal])) { ?><small class="kos-signal__desc"><?php echo h($_signalLabels[$_signal]); ?></small><?php } ?>
                </label>
              </div>
            <?php } ?>
          </div>

          <div class="kos-section">
            <h3 class="kos-section__title"><?php echo t('Tracking codes') ?></h3>
            <p class="kos-hint"><?php echo t('Optional. Scripts added here load after the visitor accepts this category.'); ?></p>
            <details class="kos-more"<?php echo $_hasCodes ? ' open' : ''; ?>>
              <summary class="kos-more__summary"><?php echo t('Header, body and footer codes'); ?></summary>
              <div class="kos-more__body">
                <div class="form-group">
                  <?php echo $form->label('code_header', t('Header Tracking Codes')) ?>
                  <?php echo $form->textarea('code_header', isset($_post['code_header']) ? $_post['code_header'] : '', ['style' => 'height: 110px;', 'class' => 'form-control font-monospace', 'spellcheck' => 'false']) ?>
                </div>
                <div class="form-group">
                  <?php echo $form->label('code_body', t('Body Tracking Codes')) ?>
                  <?php echo $form->textarea('code_body', isset($_post['code_body']) ? $_post['code_body'] : '', ['style' => 'height: 110px;', 'class' => 'form-control font-monospace', 'spellcheck' => 'false']) ?>
                </div>
                <div class="form-group">
                  <?php echo $form->label('code_footer', t('Footer Tracking Codes')) ?>
                  <?php echo $form->textarea('code_footer', isset($_post['code_footer']) ? $_post['code_footer'] : '', ['style' => 'height: 110px;', 'class' => 'form-control font-monospace', 'spellcheck' => 'false']) ?>
                </div>
                <div class="form-check">
                  <?php echo $form->checkbox('code_always_load', '1', !empty($_post['code_always_load']) ? '1' : '0') ?>
                  <label class="form-check-label" for="code_always_load"><?php echo t('Load these codes on every page, regardless of consent') ?></label>
                </div>
                <p class="kos-hint"><?php echo t('Only for a GTM/gtag loader that reads the Consent Mode signals.'); ?></p>
              </div>
            </details>
          </div>

          <div class="kos-section">
            <h3 class="kos-section__title"><?php echo t('Cookie cleanup') ?></h3>
            <p class="kos-hint"><?php echo t('Cookies removed when a visitor withdraws consent.'); ?></p>
            <details class="kos-more"<?php echo $_hasCleanup ? ' open' : ''; ?>>
              <summary class="kos-more__summary"><?php echo t('Cookies to remove'); ?></summary>
              <div class="kos-more__body">
                <div class="form-group">
                  <?php echo $form->label('managed_cookie_names', t('Cookie names')) ?>
                  <?php echo $form->textarea('managed_cookie_names', isset($_post['managed_cookie_names']) ? $_post['managed_cookie_names'] : '', ['style' => 'height: 110px;', 'class' => 'form-control font-monospace', 'spellcheck' => 'false', 'placeholder' => "_ga\n_gid\n_ga_*"]) ?>
                  <p class="kos-hint"><?php echo t('One name per line. Use * at the end for a prefix, e.g. %s.', '_ga_*'); ?></p>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $form->label('managed_cookie_domain', t('Cookie domain (optional)')) ?>
                      <?php echo $form->text('managed_cookie_domain', isset($_post['managed_cookie_domain']) ? $_post['managed_cookie_domain'] : '', ['placeholder' => 'example.com']) ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $form->label('managed_cookie_path', t('Cookie path')) ?>
                      <?php echo $form->text('managed_cookie_path', isset($_post['managed_cookie_path']) ? $_post['managed_cookie_path'] : '/', ['placeholder' => '/']) ?>
                    </div>
                  </div>
                </div>
                <p class="kos-hint"><?php echo t('Your own site cookies only. Do not add %s, %s or %s. Cookies on other domains and HttpOnly cookies cannot be removed.', 'kos_cookie_consent_id', 'consent', 'consent_version'); ?></p>
              </div>
            </details>
          </div>
        </fieldset>

        <div class="ccm-dashboard-form-actions-wrapper">
          <div class="ccm-dashboard-form-actions d-flex justify-content-between">
            <div class="col-12 col-sm-6">
              <a href="<?php echo $controller->kosdesign->getLink() ?>" class="btn btn-default pull-left"><?php echo t('Back') ?></a>
              <?php if (isset($_type) && $_type === 'add') { ?>
                <a href="<?php echo $controller->kosdesign->getLink('add') ?>" class="btn btn-danger pull-left"><?php echo t('Reset') ?></a>
              <?php } ?>
              <?php if (isset($id) && isset($_type) && $_type === 'edit') { ?>
                <a href="<?php echo $controller->kosdesign->getLink('edit/' . $id) ?>" class="btn btn-danger pull-left"><?php echo t('Reset') ?></a>
              <?php } ?>
            </div>
            <div class="col-12 col-sm-6 text-end">
              <input type="hidden" name="token" value="<?php echo $_token; ?>">
              <input type="submit" class="btn btn-primary pull-right btn ccm-input-submit" value="<?php echo t('Save') ?>">
            </div>
          </div>
        </div>
      </form>
    </div>
    <script>
      $(function() {
        var $form = $('#site_id').closest('form');

        function sitePanel(siteId) {
          return $('.kos-lang[data-site="' + siteId + '"]');
        }

        function refreshStatus($site) {
          $site.find('.kos-lang__pane').each(function() {
            var $pane = $(this);
            var key = $pane.data('lang-pane');
            var done = $.trim($pane.find('.kos-lang__name').val()) !== '';
            var $badge = $site.find('.kos-lang__tab[data-lang-target="' + key + '"] .kos-lang__status');
            $badge.toggleClass('is-done', done).toggleClass('is-missing', !done).text(done ? '\u2713' : <?php echo json_encode(t('Required')); ?>);
            if (done) {
              $pane.find('.kos-lang__error').hide();
            }
          });
        }

        function activate($site, key) {
          $site.find('.kos-lang__tab').removeClass('is-active').filter('[data-lang-target="' + key + '"]').addClass('is-active');
          $site.find('.kos-lang__pane').removeClass('is-active').filter('[data-lang-pane="' + key + '"]').addClass('is-active');
        }

        function showSite(siteId) {
          $('.kos-lang').removeClass('d-none').hide();
          if (!siteId) {
            return;
          }
          var $site = sitePanel(siteId).show();
          refreshStatus($site);
          var $panes = $site.find('.kos-lang__pane');
          var anyFilled = $panes.find('.kos-lang__name').filter(function() { return $.trim($(this).val()) !== ''; }).length > 0;
          if (anyFilled) {
            var $firstMissing = $panes.filter(function() { return $.trim($(this).find('.kos-lang__name').val()) === ''; }).first();
            if ($firstMissing.length) {
              activate($site, $firstMissing.data('lang-pane'));
            }
          }
        }

        showSite($('#site_id').val());
        $('#site_id').on('change', function() { showSite($(this).val()); });

        $form.on('click', '.kos-lang__tab', function() {
          activate($(this).closest('.kos-lang'), $(this).data('lang-target'));
        });

        $form.on('input change', '.kos-lang__name', function() {
          refreshStatus($(this).closest('.kos-lang'));
        });

        // Stop the submit and jump to the first language tab that still needs a name.
        $form.on('submit', function(e) {
          var siteId = $('#site_id').val();
          if (!siteId) {
            return;
          }
          var $site = sitePanel(siteId);
          var $missing = $site.find('.kos-lang__pane').filter(function() {
            return $.trim($(this).find('.kos-lang__name').val()) === '';
          });
          if ($missing.length) {
            e.preventDefault();
            refreshStatus($site);
            activate($site, $missing.first().data('lang-pane'));
            $missing.first().find('.kos-lang__error').show();
            $missing.first().find('.kos-lang__name').trigger('focus');
          }
        });
      });
    </script>
  <?php } else { ?>
  <?php
    $_siteChoices = [];
    foreach ((array) $controller->kosdesign->getSiteLanguage() as $_s) {
      $_siteChoices[$_s['site_id']] = $_s['site_name'];
    }
    $_multiSite = count($_siteChoices) > 1;
  ?>
  <?php if ($_multiSite) { ?>
    <form class="kos-filter" method="get" action="<?php echo $view->action('') ?>">
      <label for="site_id"><?php echo t('Store'); ?></label>
      <?php echo $form->select('site_id', ['' => t('All Stores')] + $_siteChoices, isset($_post['site_id']) ? $_post['site_id'] : null, ['onchange' => 'this.form.submit()']); ?>
    </form>
  <?php } ?>
  <table border="0" cellspacing="1" cellpadding="0" class="table table-striped setting-table" id="cookie-sort-table">
    <thead>
      <tr>
        <th class="td-other" style="width: 48px;"></th>
        <th class="td-name"><?php echo t('Category'); ?></th>
        <th class="td-other"><?php echo t('Consent signals'); ?></th>
        <th class="td-other"><?php echo t('Status'); ?></th>
        <th class="td-other"><?php echo t('Updated'); ?> (<?php echo h($controller->kosdesign->getDisplayTimezoneLabel()); ?>)</th>
        <th class="td-action"></th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($result)) { ?>
        <?php foreach ($result as $val) { ?>
          <?php $_rowSignals = $controller->kosdesign->parseConsentModeTypes(isset($val['consent_mode_types']) ? $val['consent_mode_types'] : ''); ?>
          <tr draggable="true" data-sort-id="<?php echo (int) $val['id']; ?>" data-site-id="<?php echo (int) $val['site_id']; ?>">
            <td class="text-center align-middle kos-table__handle" title="<?php echo h(t('Drag to change the order')); ?>">&#x2630;</td>
            <td>
              <strong><?php echo h($val['name']); ?></strong>
              <?php if ($_multiSite) { ?><div class="kos-hint kos-hint--flush"><?php echo h($val['store_name']); ?></div><?php } ?>
            </td>
            <td>
              <?php if ($_rowSignals) { ?>
                <?php foreach ($_rowSignals as $_sig) { ?><span class="kos-badge kos-badge--signal"><?php echo h($_sig); ?></span><?php } ?>
              <?php } else { ?>
                <span class="text-muted">&mdash;</span>
              <?php } ?>
            </td>
            <td>
              <?php if (!$val['active']) { ?>
                <span class="kos-badge kos-badge--hidden"><?php echo t('Hidden'); ?></span>
              <?php } elseif ($val['always']) { ?>
                <span class="kos-badge kos-badge--always"><?php echo t('Always on'); ?></span>
              <?php } else { ?>
                <span class="kos-badge kos-badge--optional"><?php echo t('Visitor can choose'); ?></span>
              <?php } ?>
            </td>
            <td><?php echo h($controller->kosdesign->formatLogTimestamp($val['updated'])); ?></td>
            <td class="text-end td-action">
              <div class="btn-group">
                <a class="btn btn-secondary btn-sm" href="<?php echo $controller->kosdesign->getLink('edit', $val['id']); ?>"><?php echo t('EDIT'); ?></a>
                <a href="#" class="btn btn-danger btn-sm delete-form-answers" data-id="<?php echo $val['id']; ?>" data-name="<?php echo h($val['name']); ?>" data-button="data-delete"><?php echo t('DELETE'); ?></a>
              </div>
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr>
          <td colspan="99">
            <p class="empty text-center kos-table__empty"><?php echo t('No categories yet. Click "Add default categories" to get started.'); ?></p>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <div class="kos-settings">
  <h3 class="kos-settings__title"><?php echo t('Settings'); ?></h3>
  <div class="kos-settings__grid">
    <div class="kos-card">
      <h4 class="kos-card__title"><?php echo t('Google Consent Mode V2'); ?></h4>
      <p class="kos-hint"><?php echo t('Tells Google tags what the visitor allowed. Needed for Google Analytics and Google Ads.'); ?></p>
      <form method="post" action="<?php echo $view->action('save_consent_mode'); ?>">
        <div class="form-check kos-field">
          <input type="checkbox" class="form-check-input" id="consent_mode_enabled" name="consent_mode_enabled" value="1"<?php echo !empty($_consentModeSettings['enabled']) ? ' checked' : ''; ?>>
          <label class="form-check-label" for="consent_mode_enabled"><?php echo t('Turn on Consent Mode V2'); ?></label>
        </div>
        <div class="kos-field">
          <label for="consent_mode_wait_for_update"><?php echo t('Wait for visitor choice (ms)'); ?></label>
          <input type="number" min="0" max="<?php echo (int) $_consentModeSettings['max_wait_for_update']; ?>" step="50" class="form-control" id="consent_mode_wait_for_update" name="consent_mode_wait_for_update" value="<?php echo (int) $_consentModeSettings['wait_for_update']; ?>">
          <p class="kos-hint kos-hint--tight"><?php echo t('Recommended: 500.'); ?></p>
        </div>
        <details class="kos-more">
          <summary class="kos-more__summary"><?php echo t('Advanced options'); ?></summary>
          <div class="kos-more__body">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="consent_mode_ads_data_redaction" name="consent_mode_ads_data_redaction" value="1"<?php echo !empty($_consentModeSettings['ads_data_redaction']) ? ' checked' : ''; ?>>
              <label class="form-check-label" for="consent_mode_ads_data_redaction"><?php echo t('Hide ad click IDs while ad cookies are denied'); ?> <code>ads_data_redaction</code></label>
            </div>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="consent_mode_url_passthrough" name="consent_mode_url_passthrough" value="1"<?php echo !empty($_consentModeSettings['url_passthrough']) ? ' checked' : ''; ?>>
              <label class="form-check-label" for="consent_mode_url_passthrough"><?php echo t('Keep ad click info in page URLs'); ?> <code>url_passthrough</code></label>
            </div>
          </div>
        </details>
        <input type="hidden" name="token" value="<?php echo $_tokenConsentMode; ?>">
        <button type="submit" class="btn btn-primary"><?php echo t('Save'); ?></button>
      </form>
    </div>
    <div class="kos-card">
      <h4 class="kos-card__title"><?php echo t('Remember the visitor choice'); ?></h4>
      <p class="kos-hint"><?php echo t('How long the choice is kept before the banner is shown again.'); ?></p>
      <form method="post" action="<?php echo $view->action('save_preferences'); ?>">
        <div class="kos-field">
          <label for="consent_cookie_lifetime_days"><?php echo t('Retention (days)'); ?></label>
          <input
            type="number"
            min="<?php echo isset($_consentCookieSettings['min_days']) ? (int) $_consentCookieSettings['min_days'] : 1; ?>"
            max="<?php echo isset($_consentCookieSettings['max_days']) ? (int) $_consentCookieSettings['max_days'] : 400; ?>"
            step="1"
            class="form-control"
            id="consent_cookie_lifetime_days"
            name="consent_cookie_lifetime_days"
            value="<?php echo isset($_consentCookieSettings['consent_cookie_lifetime_days']) ? (int) $_consentCookieSettings['consent_cookie_lifetime_days'] : 30; ?>"
            required
          >
          <p class="kos-hint kos-hint--tight"><?php echo t('Allowed: %s to %s days.', isset($_consentCookieSettings['min_days']) ? (int) $_consentCookieSettings['min_days'] : 1, isset($_consentCookieSettings['max_days']) ? (int) $_consentCookieSettings['max_days'] : 400); ?></p>
        </div>
        <details class="kos-more">
          <summary class="kos-more__summary"><?php echo t('Technical details'); ?></summary>
          <div class="kos-more__body">
            <?php echo t('Cookies set by this package: %s, %s and %s (Path=/, SameSite=Lax, Secure on HTTPS). Cookies created by tracking scripts follow those scripts own settings.', 'kos_cookie_consent_id', 'consent', 'consent_version'); ?>
          </div>
        </details>
        <input type="hidden" name="token" value="<?php echo $_tokenSettings; ?>">
        <button type="submit" class="btn btn-primary"><?php echo t('Save'); ?></button>
      </form>
    </div>
  </div>
  </div>
  <?php if ($pages > 1) { ?>
    <div class="ccm-search-results-pagination">
      <div class="ccm-pagination-wrapper">
        <?php
        for ($i = 1; $pages >= $i; $i++) {
          if ($i == 1 && $i == $current) {
            echo '<ul class="pagination"><li class="page-item prev disabled"><span class="page-link">' . t('Prev') . '</span></li>';
          } elseif ($i == 1 && $i != $current) {
            echo '<ul class="pagination"><li class="page-item prev"><a class="page-link" href="' . $controller->kosdesign->getLink() . '?page=' . ($current - 1) . '">' . t('Prev') . '</a></li>';
          }

          if ($i == $current) {
            echo '<li class="page-item active"><span class="page-link">' . $i . '<span class="sr-only">(current)</span></span></li>';
          } else {
            echo '<li class="page-item"><a class="page-link" href="' . $controller->kosdesign->getLink() . '?page=' . $i . '">' . $i . '</a></li>';
          }

          if ($i == $pages && $i == $current) {
            echo '<li class="page-item next disabled"><span class="page-link">' . t('Next') . '</span></li></ul>';
          } elseif ($i == $pages && $i != $current) {
            echo '<li class="page-item next"><a class="page-link" href="' . $controller->kosdesign->getLink() . '?page=' . ($current + 1) . '">' . t('Next') . '</a></li></ul>';
          }
        }
        ?>
      </div>
    </div>
  <?php } ?>
  <script>
    (function() {
      var table = document.getElementById('cookie-sort-table');
      if (!table) {
        return;
      }

      var tbody = table.querySelector('tbody');
      if (!tbody) {
        return;
      }

      var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-sort-id]'));
      if (rows.length < 2) {
        return;
      }

      var dragRow = null;

      function getAfterElement(container, y) {
        var candidates = Array.prototype.slice.call(container.querySelectorAll('tr[data-sort-id]:not(.is-dragging)'));
        var closest = {offset: Number.NEGATIVE_INFINITY, element: null};

        candidates.forEach(function(child) {
          var box = child.getBoundingClientRect();
          var offset = y - box.top - box.height / 2;
          if (offset < 0 && offset > closest.offset) {
            closest = {offset: offset, element: child};
          }
        });

        return closest.element;
      }

      function persistSort() {
        var ids = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-sort-id]')).map(function(row) {
          return row.getAttribute('data-sort-id');
        });

        $.concreteAjax({
          url: '<?php echo $view->action('save_sort_order'); ?>',
          data: {
            ids: ids,
            site_id: '<?php echo isset($_post['site_id']) ? (int) $_post['site_id'] : 0; ?>',
            token: '<?php echo $_tokenSort; ?>'
          },
          success: function() {
            var index = 1;
            tbody.querySelectorAll('tr[data-sort-id]').forEach(function(row) {
              var sortCell = row.querySelector('.kos-sort');
              if (sortCell) {
                sortCell.textContent = index;
              }
              index++;
            });
          }
        });
      }

      rows.forEach(function(row) {
        row.addEventListener('dragstart', function() {
          dragRow = row;
          row.classList.add('is-dragging');
        });

        row.addEventListener('dragend', function() {
          row.classList.remove('is-dragging');
          dragRow = null;
          persistSort();
        });
      });

      tbody.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (!dragRow) {
          return;
        }

        var afterElement = getAfterElement(tbody, e.clientY);
        if (afterElement == null) {
          tbody.appendChild(dragRow);
        } else {
          tbody.insertBefore(dragRow, afterElement);
        }
      });
    })();

    $('a[data-button=data-delete]').on('click', function() {
      return confirm('Are you sure you want to remove "' + $(this).attr('data-name') + '"? ');
    });

    $('.setting').on('click', 'a[data-button=data-delete]', function() {
      $.concreteAjax({
        url: '<?php echo $controller->kosdesign->getLink('remove'); ?>',
        data: {
          id: $(this).attr('data-id'),
          token: '<?php echo $_tokenDelete; ?>'
        },
        success: function(r) {
          if (r) {
            window.location.href = r.redirectURL;
          }
          jQuery.fn.dialog.closeTop();
        }
      });
      return false;
    });
  </script>
<?php } ?>
</div>
