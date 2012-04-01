<?php

$wordefinery_version = '0.7.5.3';

if ( !defined('WORDEFINERY') ) return $wordefinery_version;

if ( defined('WORDEFINERY') && !class_exists('Wordefinery') ) :

final class Wordefinery {

    static private $lib_version = '';
    static private $PHP52 = false;
    static private $registry = array();
    static private $autoload = array();
    static private $options = array();
    static private $plugins = array();
    static private $notice = array();
    static private $error = array();
    static private $compat = array();

    static function Register($path, $class, $version = '0.0') {
        $path = plugin_basename($path);
        self::$registry['plugin'][$class]['entry'][$path] = $version;
        add_action('plugins_loaded', 'Wordefinery::Init');
    }

    static function Init() {
        if (!self::_compat_require('php53')) {
            if (!self::_compat_require('wordpress, php52', true)) {
                return;
            } else {
                self::$lib_version = '5.2' . DIRECTORY_SEPARATOR;
                self::$PHP52 = true;
            }
        }
        if (!self::_compat_require('wordpress', true)) return;
        if (!self::_compat_require('wordpress30')) {
            self::$lib_version = '5.2' . DIRECTORY_SEPARATOR;
            self::$PHP52 = true;
        }

        load_plugin_textdomain( 'wordefinery', false, plugin_basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'languages' );
        $req = dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$lib_version . 'functions.php';
        if (file_exists($req)) require_once($req);

        $registry = get_option('wordefinery_registry');

        $_autoload = 0;
        $_database = 0;
        foreach (self::$registry['plugin'] as $class=>&$item) {
            uasort($item['entry'], 'version_compare');
            $item['entry'] = array_reverse($item['entry'], true);
            list($p, $v) = each ($item['entry']);
            $item['version'] = $v;
            $item['path'] = $p;
        }

        foreach (self::$registry['plugin'] as $c=>$p) {
            $req = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $p['path'] . DIRECTORY_SEPARATOR . 'lib'. DIRECTORY_SEPARATOR . self::$lib_version  . $c . '.php';
            if (!file_exists($req)) continue;
            require_once($req);

            $class = '\\wordefinery\\' . $c;
            if (self::$PHP52) $class = 'Wordefinery_' . $c;

            if (class_exists($class)) {
                if (!isset($registry['plugin'][$c])) {
                    // todo: activate
                    $_database = 1;
                    $_autoload = 1;
                    self::$registry['plugin'][$c]['autoload'] = self::_get_autoload($p['path']);
                    self::$registry['plugin'][$c]['database'] = self::_get_database($p['path']);
                } else {
                    if ($registry['plugin'][$c]['version'] != $p['version']) {
                        $_database = 1;
                        $_autoload = 1;
                        self::$registry['plugin'][$c]['autoload'] = self::_get_autoload($p['path']);
                        self::$registry['plugin'][$c]['database'] = self::_get_database($p['path']);
                    }
                    if ($registry['plugin'][$c]['path'] != $p['path']) {
                        $_autoload = 1;
                        self::$registry['plugin'][$c]['autoload'] = self::_get_autoload($p['path']);
                    }
                    if (!isset(self::$registry['plugin'][$c]['autoload'])) {
                        self::$registry['plugin'][$c]['autoload'] = $registry['plugin'][$c]['autoload'];
                    }
                    if (!isset(self::$registry['plugin'][$c]['database'])) {
                        self::$registry['plugin'][$c]['database'] = $registry['plugin'][$c]['database'];
                    }
                    unset($registry['plugin'][$c]);
                }
                self::$registry['plugin'][$c]['version'] = $v;
            } else {
                unset(self::$registry['plugin'][$c]);
            }
        }

        if (isset($registry['plugin']) && is_array($registry['plugin']) && count($registry['plugin'])) {
            // todo: deactivate
            $_autoload = 1;
            $_database = 1;
        }

        if ($_autoload) {
            $a = array();
            foreach (self::$registry['plugin'] as $c=>$p) {
                if (isset($p['autoload']) && is_array($p['autoload'])) {
                    foreach ($p['autoload'] as $comp=>$ca) {
                        if (!isset($a[$comp]) || version_compare($a[$comp][$version], $ca['version']) < 0) {
                            $a[$comp] = $ca;
                            $a[$comp]['path'] = $p['path'];
                        }
                    }
                }
            }
            $autoload = array();
            foreach ($a as $comp) {
                $p = $comp['path'];
                unset($comp['version']);
                unset($comp['path']);
                foreach ($comp as &$item) {
                    $item = $p . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $item;
                }
                $autoload = array_merge($autoload, $comp);
            }
            self::$registry['autoload'] = $autoload;
        } else {
            self::$registry['autoload'] = $registry['autoload'];
        }

        if ($_database) {
            foreach (self::$registry['plugin'] as $c=>$p) {
            }
        } else {
            self::$registry['database'] = $registry['database'];
        }

        if ($_autoload || $_database) {
            update_option('wordefinery_registry', self::$registry);
        }

        self::$autoload = self::$registry['autoload'];
        if (self::$PHP52) {
            foreach (self::$autoload as $c=>$f) {
                $ca = explode('\\', trim($c, '\\'));
                foreach ($ca as $k=>&$ci) {
                    if ($k<count($ca)-1) $ci = ucwords($ci);
                }
                unset(self::$autoload[$c]);
                $c = implode('_', $ca);
                self::$autoload[$c] = str_replace('lib' . DIRECTORY_SEPARATOR, 'lib' . DIRECTORY_SEPARATOR . self::$lib_version, $f);
            }
        }
        spl_autoload_register('Wordefinery::Autoloader');

        $f = '\\wordefinery\\Settings::bind';
        if (self::$PHP52) $f = 'Wordefinery_Settings::bind';
        self::$options = call_user_func($f, 'wordefinery');

        foreach (self::$registry['plugin'] as $c=>$p) {
            $class = '\\wordefinery\\' . $c;
            if (self::$PHP52) $class = 'Wordefinery_' . $c;
            if (!class_exists($class)) continue;
            if (defined($class.'::DB') && constant($class.'::DB')) {
                if (self::UseDB()) {
                    self::$plugins[$c] = new $class($p['path']);
                } else {
                    self::$compat[$c.'_db'] = 1;
                }
            } else {
                self::$plugins[$c] = new $class($p['path']);
            }
        }
    }

    static function Autoloader($class) {
        if (!self::$PHP52) $class = '\\'.$class;
        if (isset(self::$autoload[$class])) {
            include(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . self::$autoload[$class]);
            return true;
        }
        return false;
    }

    static function AdminNotice() {
        if (count(self::$error)) {
            echo '<div class="error">';
            foreach (self::$error as $n) {
                echo "<p>{$n}</p>\n";
            }
            echo "</div>\n";
        }
        if (count(self::$notice)) {
        foreach (self::$notice as $n) {
                echo "<div class='updated fade'><p>{$n}</p></div>\n";
            }
        }
    }

    static function UseDB() {
        static $db_init = 0;

        if ($db_init) return true;
        if (!self::$autoload['\\wordefinery\\MySQLiAdapter']) {
            self::Notice('', __(': DB init error'), 1);
            return false;
        }

        $config = array(
                'host' => DB_HOST,
                'user' => DB_USER,
                'pass' => DB_PASSWORD,
                'name' => DB_NAME,
                'charset' => DB_CHARSET,
                'collate' => DB_COLLATE,
            );

        //todo: init db

        $db_init = 1;
        return true;
    }

    static function SetOption($key = null, $value = null) {
        self::$options[$key] = $value;
        self::$options->commit();
    }

    static function Option($section) {
        return self::$options[$section]->__toArray();
    }

    static function Plugin($class) {
        if (isset(self::$plugins[$class]))
            return self::$plugins[$class];
    }

    static private function _get_autoload($path) {
        static $cache = array();
        $inc = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . '_autoload.php';
        if (!isset($cache[$path])) {
            if (file_exists($inc)) {
                $cache[$path] = include($inc);
            } else {
                $cache[$path] = array();
            }
        }
        return $cache[$path];
    }

    static private function _get_database($path) {
        static $cache = array();
        $inc = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . '_database.php';
        if (!isset($cache[$path])) {
            if (file_exists($inc)) {
                $cache[$path] = include($inc);
            } else {
                $cache[$path] = array();
            }
        }
        return $cache[$path];
    }

    static private function _compat_require($components, $req = false) {
        return self::Requires('', $components, $req);
    }

    static function Requires($plugin, $components, $req = false) {
        $php_tracker = '<img src="http://wordefinery.com/i/php-version.gif?'.PHP_VERSION.'" width="1" height="1" border="0" alt="" />';
        $wp_tracker = '<img src="http://wordefinery.com/i/wp-version.gif?'.$GLOBALS['wp_version'].'" width="1" height="1" border="0" alt="" />';
        if (!is_array($components)) $components = explode(',', $components);
        $ret = true;
        foreach ($components as $component) {
            switch (trim($component)) {
                case 'php_version':
                case 'php':
                case 'php53':
                    if (!isset(self::$compat['php53'])) self::$compat['php53'] = version_compare(PHP_VERSION, '5.3.0') >= 0;
                    if ($req && !self::$compat['php53']) self::Notice($plugin, __('require PHP version 5.3 or greater') . $php_tracker, 1);
                    $ret = $ret && self::$compat['php53'];
                    break;
                case 'php52':
                    if (!isset(self::$compat['php52'])) self::$compat['php52'] = version_compare(PHP_VERSION, '5.2.0') >= 0;
                    if ($req && !self::$compat['php52']) self::Notice($plugin, __('require PHP version 5.2 or greater') . $php_tracker , 1);
                    $ret = $ret && self::$compat['php52'];
                    break;
                case 'wordpress33':
                    if (!isset(self::$compat['wordpress33'])) self::$compat['wordpress33'] = version_compare($GLOBALS['wp_version'], '3.3') >= 0;
                    if ($req && !self::$compat['wordpress33']) self::Notice($plugin, __('require WordPress version 3.3 or greater') . $wp_tracker, 1);
                    $ret = $ret && self::$compat['wordpress33'];
                    break;
                case 'wordpress32':
                    if (!isset(self::$compat['wordpress32'])) self::$compat['wordpress32'] = version_compare($GLOBALS['wp_version'], '3.2') >= 0;
                    if ($req && !self::$compat['wordpress32']) self::Notice($plugin, __('require WordPress version 3.2 or greater') . $wp_tracker, 1);
                    $ret = $ret && self::$compat['wordpress32'];
                    break;
                case 'wordpress31':
                    if (!isset(self::$compat['wordpress31'])) self::$compat['wordpress31'] = version_compare($GLOBALS['wp_version'], '3.1') >= 0;
                    if ($req && !self::$compat['wordpress31']) self::Notice($plugin, __('require WordPress version 3.1 or greater') . $wp_tracker, 1);
                    $ret = $ret && self::$compat['wordpress31'];
                    break;
                case 'wordpress30':
                    if (!isset(self::$compat['wordpress30'])) self::$compat['wordpress30'] = version_compare($GLOBALS['wp_version'], '3.0') >= 0;
                    if ($req && !self::$compat['wordpress30']) self::Notice($plugin, __('require WordPress version 3.0 or greater') . $wp_tracker, 1);
                    $ret = $ret && self::$compat['wordpress30'];
                    break;
                case 'wordpress29':
                    if (!isset(self::$compat['wordpress29'])) self::$compat['wordpress29'] = version_compare($GLOBALS['wp_version'], '2.9') >= 0;
                    if ($req && !self::$compat['wordpress29']) self::Notice($plugin, __('require WordPress version 2.9 or greater') . $wp_tracker, 1);
                    $ret = $ret && self::$compat['wordpress29'];
                    break;
                case 'wordpress28':
                    if (!isset(self::$compat['wordpress28'])) self::$compat['wordpress28'] = version_compare($GLOBALS['wp_version'], '2.8') >= 0;
                    if ($req && !self::$compat['wordpress28']) self::Notice($plugin, __('require WordPress version 2.8 or greater') . $wp_tracker, 1);
                    $ret = $ret && self::$compat['wordpress28'];
                    break;
                case 'wordpress':
                case 'wp_version':
                case 'wordpress27':
                    if (!isset(self::$compat['wordpress27'])) self::$compat['wordpress27'] = version_compare($GLOBALS['wp_version'], '2.7') >= 0;
                    if ($req && !self::$compat['wordpress27']) self::Notice($plugin, __('require WordPress version 2.7 or greater') . $wp_tracker, 1);
                    $ret = $ret && self::$compat['wordpress27'];
                    break;
                case 'mysqli':
                case 'php_mysqli':
                    self::$compat['php_mysqli'] = class_exists('mysqli');
                    $ret = $ret && self::$compat['php_mysqli'];
                    break;
                default:
                    $ret = $ret && true;
                    break;
            }
        }
        return $ret;
    }

    static function Notice($plugin, $notice, $error = 0) {
        if ($plugin == '') $plugin = 'Wordefinery plugins';
        else $plugin = "<b>$plugin</b>:";
        $notice = "{$plugin} {$notice}";
        if ($error) {
            if (!in_array($notice, self::$error)) self::$error[] = $notice;
        } else {
        	if (!in_array($notice, self::$notice)) self::$notice[] = $notice;
        }
        add_action( 'admin_notices', 'Wordefinery::AdminNotice');
    }

}

endif;
