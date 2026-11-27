<?php

namespace Concrete\Package\KosCookieConsent\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\KosCookieConsent\Src\Service\CookieConsentService;

defined('C5_EXECUTE') or die('Access Denied.');

class CookieConsent extends DashboardPageController
{
    public $kosdesign;
    public $db;

    public function on_start()
    {
        parent::on_start();

        $this->kosdesign = $this->app->make(CookieConsentService::class);
        $this->db = $this->kosdesign->getConnection();
        $this->kosdesign->setLink();
    }

    /**
     * Load a compiled dashboard stylesheet from assets/css (source in assets/scss).
     *
     * @param string $name File name without extension, e.g. "dashboard-setting"
     */
    protected function addDashboardStylesheet($name = 'dashboard-setting')
    {
        try {
            $pkg = $this->app->make('Concrete\\Core\\Package\\PackageService')->getClass('kos_cookie_consent');
            if ($pkg) {
                $this->addHeaderItem('<link rel="stylesheet" href="' . h($pkg->getRelativePath()) . '/assets/css/' . h($name) . '.css?v=' . h($pkg->getPackageVersion()) . '">');
            }
        } catch (\Throwable $e) {
            // Styling is cosmetic; never block the dashboard page.
        }
    }

    public function view()
    {
        return $this->redirect('/dashboard/cookie_consent/logs');
    }
}
