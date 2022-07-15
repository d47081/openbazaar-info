<?php

$_title   = sprintf(_('API Documentation - %s'), PROJECT_NAME);

$_styles = [
    'css/highlight.min.css',
];

$_scripts = [
    'js/highlight.min.js',
    'js/api.min.js',
];

$apiSearchParams = [
    'q' => [
        'type' => _('string'),
        'description' => _('Search query'),
        'multiple' => false,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
          '{query}'
        ],
    ],
    'm' => [
        'type' => _('string'),
        'description' => _('Filter by moderation status'),
        'multiple' => false,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
          'true',
          'false',
        ],
    ],
    'lf' => [
        'type' => _('string'),
        'description' => _('Location From presented in ISO 2 format'),
        'multiple' => true,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
          '{country_code_iso_2}|...'
        ],
    ],
    'tor' => [
        'type' => _('string'),
        'description' => _('TOR status'),
        'multiple' => false,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
          'true',
          'false',
        ],
    ],
    'lt' => [
        'type' => _('string'),
        'description' => _('Listing Type'),
        'multiple' => true,
        'required' => false,
        'listing' => true,
        'profile' => false,
        'values' => [
            'new',
            'used',
            'used-good',
            'used-excelent',
            'used-poor',
            'refurbished',
        ],
    ],
    'lc' => [
        'type' => _('string'),
        'description' => _('Listing Condition'),
        'multiple' => true,
        'required' => false,
        'listing' => true,
        'profile' => false,
        'values' => [
            'service',
            'physical-good',
            'digital-good',
            'cryptocurrency',
        ],
    ],
    'pr' => [
        'type' => _('int'),
        'description' => _('Profile Rating'),
        'multiple' => true,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
            1,
            2,
            3,
            4,
            5,
        ],
    ],
    'ps' => [
        'type' => _('string'),
        'description' => _('Profile Status'),
        'multiple' => true,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
            'online',
            'active',
            'passive',
        ],
    ],
    'id' => [
        'type' => _('string'),
        'description' => _('Peer ID'),
        'multiple' => true,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
          '{PeerID}|...'
        ],
    ],
    's' => [
        'type' => _('string'),
        'description' => _('Sort mode'),
        'multiple' => false,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
          'online',
          'added',
          'price',
        ],
    ],
    'o' => [
        'type' => _('string'),
        'description' => _('Order'),
        'multiple' => false,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
            'asc',
            'desc',
        ],
    ],
    'p' => [
        'type' => _('int'),
        'description' => _('Page number'),
        'multiple' => false,
        'required' => false,
        'listing' => true,
        'profile' => true,
        'values' => [
          '{n}'
        ],
    ],
];

require(PROJECT_DIR . '/view/api.phtml');
