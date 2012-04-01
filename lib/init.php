<?php

if ( !defined('WORDEFINERY') ) :

add_option('wordefinery_class', array(), null, 'yes');
$wordefinery_class = get_option('wordefinery_class');
$wordefinery_path = plugin_basename(dirname(__FILE__));
if (!isset($wordefinery_class[$wordefinery_path])) $wordefinery_class[$wordefinery_path] = '0.0';

$wordefinery_max_version = '0.0';
$wordefinery_max_inc = '';
foreach($wordefinery_class as $wordefinery_path=>$wordefinery_version) {
    $wordefinery_inc = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $wordefinery_path . DIRECTORY_SEPARATOR . 'Wordefinery.php';
    if (!file_exists($wordefinery_inc)) {
        unset($wordefinery_class[$wordefinery_path]);
    } else {
        $wordefinery_version = include($wordefinery_inc);
        if ($wordefinery_class[$wordefinery_path] != $wordefinery_version) {
            $wordefinery_class[$wordefinery_path] = $wordefinery_version;
        }
        if (version_compare($wordefinery_version, $wordefinery_max_version) > 0) {
            $wordefinery_max_version  = $wordefinery_version;
            $wordefinery_max_inc = $wordefinery_inc;
        }
    }
}

update_option('wordefinery_class', $wordefinery_class);
define('WORDEFINERY', true);
include($wordefinery_max_inc);

else:

$wordefinery_path = plugin_basename(dirname(__FILE__));
$wordefinery_class = get_option('wordefinery_class');
if (!isset($wordefinery_class[$wordefinery_path])) {
    $wordefinery_class[$wordefinery_path] = '0.0';
    update_option('wordefinery_class', $wordefinery_class);
}

endif;
