<?php

$title = wr___('Settings') . ' &mdash; ' . $this->plugin_title;
$parent_file = 'options-general.php';

?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<form method="post" action="options.php">
            <?php settings_fields($this->plugin_slug); ?>
            <input type="hidden" name="wordefinery[__section__]" value="<?php echo $this->plugin_slug; ?>" />
            <input type="hidden" name="wordefinery[size]" value="<?php echo $this->store->size; ?>" id="<?php echo $this->plugin_slug; ?>-size" />
            <input type="hidden" name="wordefinery[style]" value="<?php echo $this->store->style; ?>" id="<?php echo $this->plugin_slug; ?>-style" />
            <input type="hidden" name="wordefinery[color]" value="<?php echo $this->store->color; ?>" id="<?php echo $this->plugin_slug; ?>-color" />
            <input type="hidden" name="wordefinery[align]" value="<?php echo $this->store->align; ?>" id="<?php echo $this->plugin_slug; ?>-align" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php wr__e('Site Identifier') ?></th>
                    <td>
                    <input type="text" size="40" name="wordefinery[site_id]" id="<?php echo $this->plugin_slug; ?>-site_id" value="<?php echo $this->store->site_id; ?>" />
                    <input type="button" class="button" id="<?php echo $this->plugin_slug; ?>-check_site_id" value="<?php wr__e( 'Check Site Id'); ?>" />
                    <span id="<?php echo $this->plugin_slug; ?>-check_site_id-message"></span><br />
                    <i><?php wr__e('or'); ?></i><br />
                    <input type="text" size="40" name="wordefinery[site_url]" value="<?php $coder = new idna_convert(array('idn_version', 2008)); echo $coder->decode(site_url()) ?>" id="<?php echo $this->plugin_slug; ?>-site_url"  />
                    <input type="button" class="button" id="<?php echo $this->plugin_slug; ?>-get_site_id" value="<?php wr__e( 'Get Site Id'); ?>" />
                    <span id="<?php echo $this->plugin_slug; ?>-get_site_id-message"></span><br />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php wr__e('Counter') ?></th>
                    <td><div class="relative">
                    <div id="<?php echo $this->plugin_slug; ?>-counter">

                    <div class="selector size">
                    <?php foreach ($this->size_idx as $i=>$s) : ?>
                    <?php list($w, $h) = explode('x', $s); ?>
                    <a name="<?php echo $i; ?>"><img src="<?php echo WP_PLUGIN_URL.'/'.$this->path; ?>/(img)/<?php echo $s; ?>.png" width="<?php echo $w; ?>" height="<?php echo $h; ?>" alt="<?php echo $s; ?>" /></a>
                    <?php endforeach; ?>
                    </div>

                    <?php $color_idx = array(); ?>
                    <?php foreach ($this->size_idx as $i=>$s) : ?>
                    <?php list($w, $h) = explode('x', $s); ?>
                    <div class="selector style hidden" idx="<?php echo $i; ?>">
                    <?php foreach ($this->style_idx[$i] as $style=>$colors) : ?>
                    <?php $color_idx[$i.'.'.$colors] = 1; ?>
                    <a name="<?php echo $style; ?>" colors="<?php echo $colors; ?>"><img src="http://top.mail.ru/i/counters/<?php echo $style; ?>.gif" width="<?php echo $w; ?>" height="<?php echo $h; ?>" /></a>
                    <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php foreach ($color_idx as $index=>$s) : ?>
                    <?php list($i, $colors) = explode('.', $index); list($w, $h) = explode('x', $this->size_idx[$i]); $style = key($this->style_idx[$i]); ?>
                    <div class="selector color hidden" idx="<?php echo $index; ?>">
                    <?php for ($color=0; $color<$colors; $color++) : ?>
                    <a name="<?php echo $color; ?>"><img src="http://top.mail.ru/i/counters/<?php echo $style+$color; ?>.gif" width="<?php echo $w; ?>" height="<?php echo $h; ?>" /></a>
                    <?php endfor; ?>
                    </div>
                    <?php endforeach; ?>

                    </div>

                    <div id="<?php echo $this->plugin_slug; ?>-preview">
                    <div class="align">
                    <a name="left"><?php wr__e('Left') ?></a>
                    <a name="center"><?php wr__e('Center') ?></a>
                    <a name="right"><?php wr__e('Right') ?></a>
                    </div>
                    <div class="preview">
                        <img src="http://top.mail.ru/i/counters/<?php echo $this->store->style + $this->store->color; ?>.gif" />
                    </div>
                    </div>

                    </div></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php wr__e('Mode') ?></th>
                    <td>
                    <label><input type="radio" name="wordefinery[mode]" value="widget" <?php checked('widget', $this->store->mode); ?> />
                    <?php wr__e('Widget') ?></label><br/>
                    <label><input type="radio" name="wordefinery[mode]" value="footer" <?php checked('footer', $this->store->mode); ?> />
                    <?php wr__e('Footer') ?></label><br/>
                    <label><input type="radio" name="wordefinery[mode]" value="shortcode" <?php checked('shortcode', $this->store->mode); ?> />
                    <?php wr__e('Shortcode') ?></label> <code>[mailrucounter]</code><br/>
                    <code>&lt;?php do_shortcode('[mailrucounter]') ?&gt;</code> &mdash; <i><?php wr__e('Use this code in your template') ?></i><br/>
                    </td>
                </tr>
            </table>

<p class="submit">
    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>
</form>

<img src="http://wordefinery.com/i/mailru-counter.gif?wp=<?php echo $GLOBALS['wp_version']; ?>&v=<?php echo self::VERSION; ?>" width="1" height="1" border="0" alt="" />
</div>
