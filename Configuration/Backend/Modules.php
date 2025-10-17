<?php

return [
    'Sntg_module' => [
        'labels' => 'LLL:EXT:skynetaccessibilityscanner/Resources/Private/Language/BackendModule.xlf',
        'icon' => 'EXT:skynetaccessibilityscanner/Resources/Public/Icons/module-skynetaccessibilityscanner.svg',
        'iconIdentifier' => 'module-skynetaccessibilityscanner',
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'position' => ['after' => 'web'],
    ],
    'Sntg_SkynetaccessibilityscannerToolmodule' => [
        'parent' => 'Sntg_module',
        'position' => ['before' => 'top'],
        'access' => 'admin,user,group',
        'path' => '/module/Skynettechnologies/SkynetaccessibilityscannerToolmodule',
        'icon'   => 'EXT:skynetaccessibilityscanner/Resources/Public/Icons/whats_app.svg',
        'labels' => 'LLL:EXT:skynetaccessibilityscanner/Resources/Private/Language/locallang_whastappmodule.xlf',
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'extensionName' => 'Skynetaccessibilityscanner',
        'controllerActions' => [
            \Skynettechnologies\Skynetaccessibilityscanner\Controller\ToolController::class => [
                'chatSettings', 
            ],
        ],
    ],
    
];

?>