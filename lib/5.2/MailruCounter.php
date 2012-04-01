<?php

final class Wordefinery_MailruCounter {

    const VERSION = '0.8.10';
    const DB = false;
    private $path = '';
    private $_is_counter = 0;

    private $size_idx  = array(1=>'88x31', 5=>'88x40', 4=>'88x18', 2=>'88x15', 3=>'38x31');
    private $style_idx = array(1=>array(210=>15, 230=>12, 47=>15, 242=>12),
                               2=>array(170=>12),
                               3=>array(190=>15, 67=>15),
                               4=>array(82=>15, 97=>15),
                               5=>array(130=>12, 150=>12));

    function __construct($path) {
        $this->path = $path;

        $this->plugin_title = wr___('Wordefinery Mail.ru Counter');
        $this->plugin_slug = 'wordefinery-mailrucounter';

        if (!Wordefinery::Requires($this->plugin_title, 'wordpress28', true)) return;

        $this->store = Wordefinery_Settings::bind(array('wordefinery', $this->plugin_slug));

        $this->store->defvalue(array(
            'size'      => key($this->size_idx),
            'style'     => key($this->style_idx[key($this->size_idx)]),
            'color'     => 0,
            'align'     => 'center',
            'mode'      => 'widget',
        ));

        if (!isset($this->store->site_id)) {
            $coder = new idna_convert(array('idn_version', 2008));
            $url = $coder->decode(site_url());
            $site_id = $this->GetSiteId($url);
            $this->store->site_id = $site_id;
            $this->store->commit();
        }

        list($this->store->width, $this->store->height) = explode('x', $this->size_idx[$this->store->size]);
        if (!$this->store->site_id) {
            Wordefinery::Notice($this->plugin_title, sprintf(wr___('set site identifier on <a href="%1$s">plugin settings page</a>.'), 'options-general.php?page='.$this->plugin_slug.'-settings'));
        }

        add_action('admin_menu', array(&$this, 'AdminMenu'));
        add_action('admin_init', array(&$this, 'AdminInit'));

        if ($this->store->site_id) {
            switch ($this->store->mode) {
                case 'widget':
                    add_action('widgets_init', create_function('', "register_widget('Wordefinery_MailruCounterWidget');"));
                    break;
                case 'footer':
                    add_action('wp_footer', array(&$this, 'Footer'));
                    break;
                case 'shortcode':
                    add_shortcode( 'mailrucounter', array(&$this, 'Shortcode'));
                    break;
            }
            add_filter('wp_nav_menu', array(&$this, 'Counter'));
            add_filter('wp_page_menu', array(&$this, 'Counter'));
        }
    }

    function AdminInit() {
        $this->store->site_id()->validator(array($this, 'SiteIdValidator'));
        $this->store->size()->validator(array($this, 'SizeValidator'));
        $this->store->style()->validator(array($this, 'StyleValidator'));
        $this->store->color()->validator(array($this, 'ColorValidator'));
        $this->store->align()->validator(create_function('$data', "if (!in_array(\$data, array('center', 'left', 'right'))) return 'center'; ") );
        $this->store->mode()->validator(create_function('$data', "if (!in_array(\$data, array('widget', 'shortcode', 'footer'))) return 'widget'; ") );

        register_setting( $this->plugin_slug, 'wordefinery' );
        add_action('wp_ajax_get_site_id', array(&$this, 'SettingsGetSiteId'));
        add_action('wp_ajax_check_site_id', array(&$this, 'SettingsCheckSiteId'));
        wp_register_style($this->plugin_slug.'-settings', WP_PLUGIN_URL . '/' . $this->path . '/(css)/mailrucounter-settings-page.css', array(), self::VERSION );
        wp_register_script($this->plugin_slug.'-settings', WP_PLUGIN_URL . '/' . $this->path . '/(js)/mailrucounter-settings-page.js', array('jquery'), self::VERSION );
    }

    function AdminMenu() {
        $page = add_options_page(
            wr___('Settings') . ' &mdash; ' . $this->plugin_title,
            wr___('Mail.ru Counter'),
            'manage_options',
            $this->plugin_slug . '-settings',
            array(&$this, 'SettingsPage')
        );

        $slug = $this->plugin_slug;
		// wp_version < 3.3.x compat
		// todo: do it in wp way
        if (version_compare($GLOBALS['wp_scripts']->registered['jquery']->ver, '1.7.1') < 0) {
            add_action( 'admin_print_styles-' . $page, create_function('', "wp_deregister_script( 'jquery' ); wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'); wp_enqueue_script( 'jquery' ); ") );
        }
        add_action( 'admin_print_styles-' . $page, create_function('', "wp_enqueue_style('{$slug}-settings');") );
        add_action( 'admin_print_scripts-' . $page, create_function('', "wp_enqueue_script('{$slug}-settings');") );
        // add_action("load-$page", array( &$this, 'help_tabs'));
    }

    function SettingsPage() {
        include(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->path . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . '5.2' . DIRECTORY_SEPARATOR . 'mailrucounter-settings-page.php');
    }

    function SettingsGetSiteId() {
        $url = $_GET['site_url'];
        $r = $this->GetSiteId($url);
        if ($r) {
            echo $r;
        } else {
            echo '<span style="color:red">';
            switch ($this->store->site_id_error()->error) {
                case 'query_error':
                    wr__e('Error');
                    break;
                case 'response_error':
                    wr__e('Error');
                    break;
                case 'not_registered':
                    echo sprintf(wr___('URL <code>%1$s</code> not registered.'), $url);
                    echo ' ';
                    wr__e('<a href="http://top.mail.ru/add" target="_blank">Register Counter</a>.');
                    break;
                default:
                    wr__e('Error');
                    break;
            }
            echo '</span>';
        }
        die;
    }

    function SettingsCheckSiteId() {
        $site_id = $_GET['site_id'];
        $r = $this->CheckSiteId($site_id);
        if ($r) {
            echo sprintf(wr___('Registered URL: <code>%1$s</code>'), $r);
        } else {
            echo '<span style="color:red">';
            switch ($this->store->site_id_error()->error) {
                case 'query_error':
                    wr__e('Error');
                    break;
                case 'response_error':
                    wr__e('Error');
                    break;
                case 'invalid_id':
                    wr__e('Invalid Site Identifier.');
                    echo ' ';
                    wr__e('<a href="http://top.mail.ru/add" target="_blank">Register Counter</a>.');
                    break;
                case 'not_registered':
                    echo sprintf(wr___('Site identifier <code>%1$s</code> not registered.'), $site_id);
                    echo ' ';
                    wr__e('<a href="http://top.mail.ru/add" target="_blank">Register Counter</a>.');
                    break;
                default:
                    wr__e('Error');
                    break;
            }
            echo '</span>';
        }
        die;
    }

    function CheckSiteId($id = null) {
        unset($this->store->site_id_error);
        $this->store->site_id_error = array();
        if (!isset($id)) $id = $this->store->site_id;
        if ($id + 0 == 0) {
            $this->store->site_id_error = array('error' => 'invalid_id');
            return false;
        }
        $url = 'http://top.mail.ru/rating?id='.$id;
        $request = new WP_Http;
        $result = $request->request($url);
        if ($result instanceof WP_Error) {
            $this->store->site_id_error = array('error' => 'query_error');
            return false;
        }
        if ($result['response']['code'] != 200) {
            $this->store->site_id_error = array('error' => 'response_error');
            return false;
        }
        $body = $result['body'];
        preg_match('|<a.+?href="/jump(.*?)>|', $body, $m);
        if ($m[1]) {
            $body = $m[1];
            preg_match('|url=(.*?)(&amp;)?&?"|', $body, $m);
            if ($m[1]) {
                $h = parse_url(urldecode($m[1]));
                $h = preg_replace('|^www\.|', '', $h['host']);
                $coder = new idna_convert(array('idn_version', 2008));
                $h = $coder->decode($h);
                return $h;
            }
        }
        $this->store->site_id_error = array('error' => 'not_registered');
        return false;
    }

    function GetSiteId($host = null) {
        if (!isset($host)) return false;
        unset($this->store->site_id_error);
        $this->store->site_id_error = array();
        $host = preg_replace('|^https?://|', '', $host);
        $host = 'http://' . $host;
        $h = parse_url(urldecode($host));
        $h['host'] = preg_replace('|^www\.|', '', $h['host']);
        $h['host'] = mb_convert_encoding($h['host'], 'cp1251', 'utf8');

        $urls = array();
        $urls[] = $h['host'];
//        $urls[] = $h['host'].'/';
        $urls[] = $h['host'].$h['path'];
//        $urls[] = $h['host'].$h['path'].'/';
//        $urls[] = 'www.'.$h['host'];
//        $urls[] = 'www.'.$h['host'].'/';
//        $urls[] = 'www.'.$h['host'].$h['path'];
//        $urls[] = 'www.'.$h['host'].$h['path'].'/';
        array_walk($urls, create_function('&$i', "\$i = str_replace('//', '/', \$i);"));
        $urls = array_unique($urls);

        $request = new WP_Http;
        foreach ($urls as $url) {
            $result = $request->request('http://top.mail.ru/?q='.$url, array('redirection'=>0));
            if ($result instanceof WP_Error) {
                $this->store->site_id_error = array('error' => 'query_error');
                return false;
            }
            if ($result['response']['code'] != 200 && $result['response']['code'] != 302) {
                $this->store->site_id_error = array('error' => 'response_error');
                continue;
            }
            if ($result['response']['code'] == 302) {
                $body = $result['headers']['location'];
                preg_match('|id=(\d+)|', $body, $m);
            	if ($m[1]) return $m[1];
       		}
        }
        if (!count($this->store->site_id_error)) $this->store->site_id_error = array('error' => 'not_registered');
        return false;
    }

    function SiteIdValidator($site_id) {
        if (!(isset($site_id) && $site_id + 0 > 0)) {
            throw new SettingsValidateException('error', wr___('Please enter a valid site identifier.'));
        } else {
            if (! $url = $this->CheckSiteId($site_id)) {
                if ($this->store->site_id_error()->error != 'query_error' && $this->store->site_id_error()->error != 'response_error' ) {
               		throw new SettingsValidateException('error', sprintf(wr___('Site identifier <code>%1$s</code> not registered. Please enter a valid site identifier.'), $site_id));
                } else {
                    throw new SettingsValidateException('updated', wr___('Site identifier validation failure.'));
                }                
            } else {
                $hurl = site_url();
                $hurl = parse_url($hurl, PHP_URL_HOST);
                $hurl = preg_replace('|^www\.|', '', $hurl);
                $coder = new idna_convert(array('idn_version', 2008));
                $hurl = $coder->decode($hurl);
                if ($url != $hurl) {
                   throw new SettingsValidateException('updated', sprintf(wr___('Site identifier <code>%1$s</code> registered to domain <code>%2$s</code>. This site domain <code>%3$s</code>.'), $site_id, $url, $hurl));
                }
            }
        }
    }

    function SizeValidator($size) {
        if (!isset($this->size_idx[$size])) return key($this->size_idx);
    }

    function StyleValidator($style) {
        $st_idx = array();
        foreach($this->size_idx as $i=>$s) $st_idx += array_fill_keys(array_keys($this->style_idx[$i]), $i);
        if (!isset($st_idx[$style])) {
            $style = key($this->style_idx[$this->store->size]);
        } elseif (isset($st_idx[$style]) && $this->store->size != $st_idx[$style]) {
            $this->store->size = $st_idx[$style];
        }
        return $style;
    }

    function ColorValidator($color) {
        if ($color > $this->style_idx[$this->store->size][$this->store->style] || $color < 0) {
            return 0;
        }
    }

    function Shortcode($args) {
        static $x = 0;
        if ($x) return;
        $x = 1;
        $s = $this->store->style + $this->color;
        return $this->Counter().
<<<END
<!-- Rating@Mail.ru counter -->
<a href="http://top.mail.ru/jump?from={$this->store->site_id}">
<img src="http://top.mail.ru/counter?id={$this->store->site_id};t={$s};l=1"
style="border:0;" height="{$this->store->height}" width="{$this->store->width}" alt="Rating@Mail.ru" /></a>
<!-- //Rating@Mail.ru counter -->
END;
    }

    function Footer() {
        $s = $this->store->style + $this->store->color;
        echo $this->Counter().
<<<END
<div style="text-align:{$this->store->align}">
<!-- Rating@Mail.ru counter -->
<a href="http://top.mail.ru/jump?from={$this->store->site_id}">
<img src="http://top.mail.ru/counter?id={$this->store->site_id};t={$s};l=1"
style="border:0;" height="{$this->store->height}" width="{$this->store->width}" alt="Rating@Mail.ru" /></a>
<!-- //Rating@Mail.ru counter -->
</div>
END;
    }

    function Counter($nav_menu = '') {
        if ($this->_is_counter) return $nav_menu;
        $this->_is_counter = 1;
        return $nav_menu.
<<<END
<!-- Rating@Mail.ru counter -->
<script type="text/javascript">//<![CDATA[
var a='';js=10;d=document;
try{a+=';r='+escape(d.referrer);}catch(e){}try{a+=';j='+navigator.javaEnabled();js=11;}catch(e){}
try{s=screen;a+=';s='+s.width+'*'+s.height;a+=';d='+(s.colorDepth?s.colorDepth:s.pixelDepth);js=12;}catch(e){}
try{if(typeof((new Array).push('t'))==="number")js=13;}catch(e){}
try{d.write('<img src="http://top.mail.ru/counter?id={$this->store->site_id};js='+js+
a+';rand='+Math.random()+'" style="border:0; height:1px; width:1px; position:absolute;" height="1" width="1" \/>');}catch(e){}//]]></script>
<noscript><img src="http://top.mail.ru/counter?js=na;id={$this->store->site_id}"
style="border:0; height:1px; width:1px; position:absolute;" height="1" width="1" /></noscript>
<!-- //Rating@Mail.ru counter -->
END;
    }
}
