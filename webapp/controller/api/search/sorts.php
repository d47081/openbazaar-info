<?php

$q = isset($_GET['q']) ? sanitizeSearchQuery($_GET['q']) : '';
$t = isset($_GET['t']) ? $_GET['t'] : '';
$s = isset($_GET['s']) ? $_GET['s'] : '';
$o = isset($_GET['o']) ? $_GET['o'] : '';

$error = false;

$json = [
    'success' => false,
    'title'   => _('Internal server error!'),
    'options' => []
];

switch ($t) {

    case 'listing':

        $options = [
            [
                'name'    => _('Relevance'),
                's'       => '',
                'o'       => '',
                'divider' => true,
            ],
            [
                'name'    => _('Price up'),
                's'       => 'price',
                'o'       => 'asc',
                'divider' => false,
            ],
            [
                'name'    => _('Price down'),
                's'       => 'price',
                'o'       => 'desc',
                'divider' => true,
            ],
            [
                'name'    => _('Newest'),
                's'       => 'added',
                'o'       => 'desc',
                'divider' => false,
            ],
            [
                'name'    => _('Oldest'),
                's'       => 'added',
                'o'       => 'asc',
                'divider' => true,
            ],
            [
                'name'    => _('Online'),
                's'       => 'online',
                'o'       => 'desc',
                'divider' => false,
            ],
        ];

        if (!in_array($s, ['online', 'price', 'added'])) {
            $s = '';
            $o = '';
        }

    break;
    case 'profile':

        $options = [
            [
                'name'    => _('Relevance'),
                's'       => '',
                'o'       => '',
                'divider' => true,
            ],
            [
                'name'    => _('Newest'),
                's'       => 'added',
                'o'       => 'desc',
                'divider' => false,
            ],
            [
                'name'    => _('Oldest'),
                's'       => 'added',
                'o'       => 'asc',
                'divider' => true,
            ],
            [
                'name'    => _('Online'),
                's'       => 'online',
                'o'       => 'desc',
                'divider' => false,
            ],
        ];

        if (!in_array($s, ['online', 'added'])) {
          $s = '';
          $o = '';
        }

    break;
    default:
        $error = true;
}

if (!$error) {

    // If search query and sort order not provided sort by newest
    if (!$q && !$s && !$o) {
         $s = 'added';
         $o = 'desc';
    }

    $title = $options[0]['name'];

    foreach ($options as $key => $option) {
        if ($option['s'] == $s && $option['o'] == $o) {

            $title = $option['name'];

            if ($option['divider'] && isset($options[$key - 1]) && isset($options[$key - 1])) {
                $options[$key - 1]['divider'] = true;
            }

            unset($options[$key]);
        }
    }

    $options = array_values($options);

    $options[count($options) - 1]['divider'] = false;

    $json = [
        'success' => true,
        'title'   => $title,
        'options' => $options
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
