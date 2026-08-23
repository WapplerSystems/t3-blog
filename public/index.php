<?php

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

call_user_func(static function () {
    $classLoader = require dirname(__DIR__) . '/.build/vendor/autoload.php';
    \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::run();

    $isInstallToolDirectAccess = false;
    if (class_exists(\TYPO3\CMS\Install\Http\Application::class)) {
        $isInstallToolDirectAccess = isset($_GET['__typo3_install']);
    }

    $container = \TYPO3\CMS\Core\Core\Bootstrap::init($classLoader, $isInstallToolDirectAccess);

    if ($container->has(\TYPO3\CMS\Core\Http\Application::class)) {
        $container->get(\TYPO3\CMS\Core\Http\Application::class)->run();
        return;
    }

    $container->get(\TYPO3\CMS\Install\Http\Application::class)->run();
});
