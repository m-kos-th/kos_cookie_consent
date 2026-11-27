<?php defined('C5_EXECUTE') or die('Access denied.'); ?>

<div class="ccm-dashboard-header-buttons"></div>

<?php
$popupEditor = Core::make('editor');
$popupEditor->setAllowFileManager(false);
$_languages = isset($popupContentLanguages) && is_array($popupContentLanguages) ? $popupContentLanguages : [];
$_popupMap = isset($popupContentMap) && is_array($popupContentMap) ? $popupContentMap : [];

// Group the languages by site so every site gets its own language tabs.
$_bySite = [];
foreach ($_languages as $_lang) {
  $_sid = (int) $_lang['site_id'];
  if (!isset($_bySite[$_sid])) {
    $_bySite[$_sid] = ['name' => (string) $_lang['site_name'], 'langs' => []];
  }
  $_bySite[$_sid]['langs'][] = $_lang;
}
$_multiSite = count($_bySite) > 1;

// Shows the built-in text under a field, with a button that copies it into the field.
$renderDefault = function (array $defaults, $key, $fieldName) {
  $text = isset($defaults[$key]) ? (string) $defaults[$key] : '';
  if ($text === '') {
    return;
  }
  ?>
  <div class="kos-default">
    <span class="kos-default__label"><?php echo t('Default'); ?>:</span>
    <span class="kos-default__text" title="<?php echo h($text); ?>"><?php echo h($text); ?></span>
    <button type="button" class="kos-default__btn" data-use-default data-target="<?php echo h($fieldName); ?>" data-default="<?php echo h($text); ?>"><?php echo t('Use default'); ?></button>
  </div>
  <?php
};
$_copyFields = ['banner_title', 'banner_summary', 'modal_title', 'modal_intro_one', 'modal_intro_two', 'preferences_title', 'save_label', 'accept_label', 'decline_label', 'settings_label'];
?>

<div class="setting">
  <form method="post" action="<?php echo $view->action('save'); ?>" id="kos-popup-content-form">
    <input type="hidden" name="token_popup_content" value="<?php echo h($_tokenPopupContent); ?>">

    <p class="kos-hint"><?php echo t('Text shown in the cookie banner. Leave a field empty to use the default text.'); ?></p>

    <?php if (!empty($_bySite)) { ?>
      <?php foreach ($_bySite as $_siteid => $_site) { ?>
        <?php if ($_multiSite) { ?>
          <h3 class="kos-section__title kos-section__title--site"><?php echo h($_site['name']); ?></h3>
        <?php } ?>
        <?php $_multi = count($_site['langs']) > 1; ?>
        <div class="kos-lang">
          <?php if ($_multi) { ?>
            <div class="kos-lang__help"><?php echo t('This site has %d languages. Fill in each language tab.', count($_site['langs'])); ?></div>
            <ul class="kos-lang__tabs" role="tablist">
              <?php foreach ($_site['langs'] as $_i => $_lang) { ?>
                <li>
                  <button type="button" class="kos-lang__tab<?php echo $_i === 0 ? ' is-active' : ''; ?>" role="tab" data-lang-target="<?php echo h($_siteid . '-' . $_lang['language']); ?>">
                    <?php echo h($_lang['text']); ?> <small class="text-muted">(<?php echo h($_lang['language']); ?>)</small>
                    <span class="kos-lang__status is-empty"><?php echo t('Default'); ?></span>
                  </button>
                </li>
              <?php } ?>
            </ul>
          <?php } ?>

          <?php foreach ($_site['langs'] as $_i => $_lang) { ?>
            <?php
              $_code = (string) $_lang['language'];
              $_copy = isset($_popupMap[$_siteid][$_code]) ? $_popupMap[$_siteid][$_code] : [];
              $_base = 'popup_copy[' . $_siteid . '][' . $_code . ']';
              $_defaults = $controller->kosdesign->getDefaultPopupCopy(isset($_lang['locale']) ? $_lang['locale'] : $_code);
              $_hasContent = false;
              foreach ($_copyFields as $_field) {
                if (isset($_copy[$_field]) && trim((string) $_copy[$_field]) !== '') {
                  $_hasContent = true;
                  break;
                }
              }
            ?>
            <div class="kos-lang__pane<?php echo (!$_multi || $_i === 0) ? ' is-active' : ''; ?>" data-lang-pane="<?php echo h($_siteid . '-' . $_code); ?>" data-has-content="<?php echo $_hasContent ? '1' : '0'; ?>">
              <?php if ($_multi) { ?>
                <p class="kos-lang__pane-title"><?php echo h($_lang['text']); ?> (<?php echo h($_code); ?>)</p>
              <?php } ?>

              <p class="kos-hint">
                <button type="button" class="kos-default__btn" data-fill-all><?php echo t('Use all defaults'); ?></button>
              </p>

              <div class="kos-lang__group">
                <h4 class="kos-lang__group-title"><?php echo t('Cookie banner'); ?></h4>
                <div class="form-group">
                  <?php echo $form->label($_base . '[banner_title]', t('Banner title')); ?>
                  <?php echo $form->text($_base . '[banner_title]', isset($_copy['banner_title']) ? $_copy['banner_title'] : '', ['class' => 'kos-lang__input', 'placeholder' => isset($_defaults['bannerTitle']) ? $_defaults['bannerTitle'] : '']); ?>
                  <?php $renderDefault($_defaults, 'bannerTitle', $_base . '[banner_title]'); ?>
                </div>
                <div class="form-group">
                  <?php echo $form->label($_base . '[banner_summary]', t('Banner summary')); ?>
                  <?php echo $popupEditor->outputStandardEditor($_base . '[banner_summary]', isset($_copy['banner_summary']) ? $_copy['banner_summary'] : ''); ?>
                  <?php $renderDefault($_defaults, 'bannerSummary', $_base . '[banner_summary]'); ?>
                </div>
              </div>

              <div class="kos-lang__group">
                <h4 class="kos-lang__group-title"><?php echo t('Preference panel'); ?></h4>
                <div class="form-group">
                  <?php echo $form->label($_base . '[modal_title]', t('Panel title')); ?>
                  <?php echo $form->text($_base . '[modal_title]', isset($_copy['modal_title']) ? $_copy['modal_title'] : '', ['class' => 'kos-lang__input', 'placeholder' => isset($_defaults['modalTitle']) ? $_defaults['modalTitle'] : '']); ?>
                  <?php $renderDefault($_defaults, 'modalTitle', $_base . '[modal_title]'); ?>
                </div>
                <div class="form-group">
                  <?php echo $form->label($_base . '[modal_intro_one]', t('Intro (paragraph 1)')); ?>
                  <?php echo $popupEditor->outputStandardEditor($_base . '[modal_intro_one]', isset($_copy['modal_intro_one']) ? $_copy['modal_intro_one'] : ''); ?>
                  <?php $renderDefault($_defaults, 'modalIntroOne', $_base . '[modal_intro_one]'); ?>
                </div>
                <div class="form-group">
                  <?php echo $form->label($_base . '[modal_intro_two]', t('Intro (paragraph 2, optional)')); ?>
                  <?php echo $popupEditor->outputStandardEditor($_base . '[modal_intro_two]', isset($_copy['modal_intro_two']) ? $_copy['modal_intro_two'] : ''); ?>
                  <?php $renderDefault($_defaults, 'modalIntroTwo', $_base . '[modal_intro_two]'); ?>
                </div>
                <div class="form-group">
                  <?php echo $form->label($_base . '[preferences_title]', t('Categories heading')); ?>
                  <?php echo $form->text($_base . '[preferences_title]', isset($_copy['preferences_title']) ? $_copy['preferences_title'] : '', ['class' => 'kos-lang__input', 'placeholder' => isset($_defaults['preferencesTitle']) ? $_defaults['preferencesTitle'] : '']); ?>
                  <?php $renderDefault($_defaults, 'preferencesTitle', $_base . '[preferences_title]'); ?>
                </div>
              </div>

              <div class="kos-lang__group">
                <h4 class="kos-lang__group-title"><?php echo t('Button labels'); ?></h4>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $form->label($_base . '[accept_label]', t('Accept All')); ?>
                      <?php echo $form->text($_base . '[accept_label]', isset($_copy['accept_label']) ? $_copy['accept_label'] : '', ['class' => 'kos-lang__input', 'placeholder' => isset($_defaults['accept']) ? $_defaults['accept'] : '']); ?>
                  <?php $renderDefault($_defaults, 'accept', $_base . '[accept_label]'); ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $form->label($_base . '[decline_label]', t('Reject All')); ?>
                      <?php echo $form->text($_base . '[decline_label]', isset($_copy['decline_label']) ? $_copy['decline_label'] : '', ['class' => 'kos-lang__input', 'placeholder' => isset($_defaults['decline']) ? $_defaults['decline'] : '']); ?>
                  <?php $renderDefault($_defaults, 'decline', $_base . '[decline_label]'); ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $form->label($_base . '[save_label]', t('Save Preferences')); ?>
                      <?php echo $form->text($_base . '[save_label]', isset($_copy['save_label']) ? $_copy['save_label'] : '', ['class' => 'kos-lang__input', 'placeholder' => isset($_defaults['save']) ? $_defaults['save'] : '']); ?>
                  <?php $renderDefault($_defaults, 'save', $_base . '[save_label]'); ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php echo $form->label($_base . '[settings_label]', t('Cookie settings link')); ?>
                      <?php echo $form->text($_base . '[settings_label]', isset($_copy['settings_label']) ? $_copy['settings_label'] : '', ['class' => 'kos-lang__input', 'placeholder' => isset($_defaults['settingsLink']) ? $_defaults['settingsLink'] : '']); ?>
                  <?php $renderDefault($_defaults, 'settingsLink', $_base . '[settings_label]'); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    <?php } else { ?>
      <p class="empty text-center kos-table__empty"><?php echo t('No site languages available.'); ?></p>
    <?php } ?>

    <div class="ccm-dashboard-form-actions-wrapper">
      <div class="ccm-dashboard-form-actions text-end">
        <button type="submit" class="btn btn-primary"><?php echo t('Save Content'); ?></button>
      </div>
    </div>
  </form>
</div>

<script>
  $(function() {
    var $form = $('#kos-popup-content-form');

    // A language counts as customized when any text field has a value or the pane had saved content on load.
    function refreshStatus($lang) {
      $lang.find('.kos-lang__pane').each(function() {
        var $pane = $(this);
        var filled = $pane.data('has-content') === 1 || $pane.data('has-content') === '1';
        if (!filled) {
          $pane.find('input.kos-lang__input').each(function() {
            if ($.trim($(this).val()) !== '') {
              filled = true;
              return false;
            }
          });
        }
        var $badge = $lang.find('.kos-lang__tab[data-lang-target="' + $pane.data('lang-pane') + '"] .kos-lang__status');
        $badge.toggleClass('is-done', filled).toggleClass('is-empty', !filled).text(filled ? '✓' : <?php echo json_encode(t('Default')); ?>);
      });
    }

    $form.on('click', '.kos-lang__tab', function() {
      var $lang = $(this).closest('.kos-lang');
      var key = $(this).data('lang-target');
      $lang.find('.kos-lang__tab').removeClass('is-active').filter('[data-lang-target="' + key + '"]').addClass('is-active');
      $lang.find('.kos-lang__pane').removeClass('is-active').filter('[data-lang-pane="' + key + '"]').addClass('is-active');
    });

    // Copy the default text into a text input or a rich-text editor.
    function setField(name, text) {
      var $el = $form.find('[name="' + name + '"]');
      if (!$el.length) {
        return;
      }
      if ($el.is('input')) {
        $el.val(text).trigger('input');
        return;
      }
      var html = '<p>' + $('<div>').text(text).html() + '</p>';
      var id = $el.attr('id');
      if (window.CKEDITOR && id && CKEDITOR.instances[id]) {
        CKEDITOR.instances[id].setData(html);
      } else {
        $el.val(html);
      }
    }

    $form.on('click', '[data-use-default]', function() {
      setField($(this).data('target'), $(this).data('default'));
    });

    $form.on('click', '[data-fill-all]', function() {
      $(this).closest('.kos-lang__pane').find('[data-use-default]').each(function() {
        setField($(this).data('target'), $(this).data('default'));
      });
    });

    $form.on('input change', 'input.kos-lang__input', function() {
      refreshStatus($(this).closest('.kos-lang'));
    });

    $('.kos-lang').each(function() {
      refreshStatus($(this));
    });
  });
</script>
