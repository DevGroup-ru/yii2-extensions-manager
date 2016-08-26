<?php
return [
    'ExtensionsManagerAdministrator' => [
        'type' => 1,
        'description' => 'This role allows to manage an extensions-manager',
        'children' => [
            'extensions-manager-view-extensions',
            'extensions-manager-install-extension',
            'extensions-manager-uninstall-extension',
            'extensions-manager-activate-extension',
            'extensions-manager-deactivate-extension',
            'extensions-manager-configure-extension',
            'extensions-manager-access-to-core-extension',
        ],
    ],
    'extensions-manager-view-extensions' => [
        'type' => 2,
    ],
    'extensions-manager-install-extension' => [
        'type' => 2,
    ],
    'extensions-manager-uninstall-extension' => [
        'type' => 2,
    ],
    'extensions-manager-activate-extension' => [
        'type' => 2,
    ],
    'extensions-manager-deactivate-extension' => [
        'type' => 2,
    ],
    'extensions-manager-configure-extension' => [
        'type' => 2,
    ],
    'extensions-manager-access-to-core-extension' => [
        'type' => 2,
    ],
];
