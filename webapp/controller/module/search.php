<?php

$module_header_form_search_q           = isset($_GET['q'])  ? sanitizeSearchQuery($_GET['q']) : false;

$module_header_form_search_id          = isset($_GET['id']) ? sanitizeRequest($_GET['id']) : false;
$module_header_form_search_lc          = isset($_GET['lc']) ? sanitizeRequest($_GET['lc']) : false;
$module_header_form_search_lt          = isset($_GET['lt']) ? sanitizeRequest($_GET['lt']) : false;
$module_header_form_search_lf          = isset($_GET['lf']) ? sanitizeRequest($_GET['lf']) : false;
$module_header_form_search_ps          = isset($_GET['ps']) ? sanitizeRequest($_GET['ps']) : false;
$module_header_form_search_pr          = isset($_GET['pr']) && in_array($_GET['pr'], [1,2,3,4,5]) ? sanitizeRequest($_GET['pr']) : false;
$module_header_form_search_m           = isset($_GET['m']) && in_array($_GET['m'], ['true','false']) ? sanitizeRequest($_GET['m']) : '';
$module_header_form_search_s           = isset($_GET['s']) && in_array($_GET['s'], ['online','added','price']) ? sanitizeRequest($_GET['s']) : '';
$module_header_form_search_o           = isset($_GET['o']) && in_array($_GET['o'], ['asc','desc']) ? sanitizeRequest($_GET['o']) : '';
$module_header_form_search_tor         = isset($_GET['tor']) && in_array($_GET['tor'], ['true','false']) ? sanitizeRequest($_GET['tor']) : '';

$module_header_form_search_t           = isset($_GET['t']) && in_array($_GET['t'], ['listing', 'profile']) ? sanitizeRequest($_GET['t']) : false;
$module_header_form_search_placeholder = sprintf(_('Over %s listings by %s profiles'),
                                                 number_format($modelListing->getTotalListings(), 0, '.', ','),
                                                 number_format($modelProfile->getTotalProfiles(), 0, '.', ','));

require(PROJECT_DIR . '/view/module/search.phtml');
