    <?php
defined('TYPO3') || die('Access denied.');

call_user_func(function () {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Skynetaccessibilityscanner',
        'Tool',
        [
            \Skynettechnologies\Skynetaccessibilityscanner\Controller\ToolController::class => 'main',
        ],
        [
            \Skynettechnologies\Skynetaccessibilityscanner\Controller\ToolController::class => 'main',
        ]
    );

    // KEEP ONLY CONFIGURATION HERE
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['security.backend.enforceContentSecurityPolicy'] = false;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['security.frontend.enforceContentSecurityPolicy'] = false;

});
