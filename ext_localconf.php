<?php
defined('TYPO3') || die('Access denied.');

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Skynettechnologies\Skynetaccessibilityscanner\Controller\ToolController;



call_user_func(function () {

    // --- Configure Extbase plugin ---
    ExtensionUtility::configurePlugin(
        'Skynettechnologies.Skynetaccessibilityscanner',
        'Tool',
        [
            ToolController::class => 'main,chatSettings',
        ],
        [
            ToolController::class => 'main,chatSettings',
        ]
    );

    // --- Register backend module icons ---
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

    $iconRegistry->registerIcon(
        'allinone-plugin-tool',
        SvgIconProvider::class,
        ['source' => 'EXT:skynetaccessibilityscanner/Resources/Public/Icons/user_plugin_whatsapp.svg']
    );

    $iconRegistry->registerIcon(
        'module-Skynetaccessibilityscanner',
        SvgIconProvider::class,
        ['source' => 'EXT:skynetaccessibilityscanner/Resources/Public/Icons/module-sntg.svg']
    );

 
    // --- Optional: disable CSP enforcement ---
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['security.backend.enforceContentSecurityPolicy'] = false;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['security.frontend.enforceContentSecurityPolicy'] = false;

    // --- Load module-specific CSS and JS ---
    $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultModule'] = [
        'SkynettechnologiesSkynetaccessibilityscannerToolModule' => [
            'cssFiles' => [
                'EXT:skynetaccessibilityscanner/Resources/Public/Css/scanning-and-monitoring-app.css',
            ],
            'jsFiles' => [
                'EXT:skynetaccessibilityscanner/Resources/Public/JavaScript/custom.js',
            ],
        ],
    ];
});

