<?php

function wr___( $t, $d = 'wordefinery' ) {
    return  __( $t, $d );
}

function wr__e( $t, $d = 'wordefinery' ) {
    return  _e( $t, $d );
}

function wr__x( $t, $c, $d = 'wordefinery' ) {
    return  _x( $t, $c, $d );
}

function wr__ex( $t, $c, $d = 'wordefinery' ) {
    return  _ex( $t, $c, $d );
}

function wr__n( $s, $p, $n, $d = 'wordefinery' ) {
    return  _n( $s, $p, $n, $d );
}

function wr__nx( $s, $p, $n, $c, $d = 'wordefinery' ) {
    return  _nx( $s, $p, $n, $c, $d );
}

function wr__n_noop( $s, $p ) {
    return  _n_noop( $s, $p );
}

function wr__nx_noop( $s, $p, $c ) {
    return  _nx_noop( $s, $p, $c );
}

function wr_esc_attr__( $t, $d = 'wordefinery' ) {
    return  esc_attr__( $t, $d );
}

function wr_esc_html__( $t, $d = 'wordefinery' ) {
    return  esc_html__( $t, $d );
}

function wr_esc_attr_e( $t, $d = 'wordefinery' ) {
    return  esc_attr_e( $t, $d );
}

function wr_esc_html_e( $t, $d = 'wordefinery' ) {
    return  esc_html_e( $t, $d );
}

function wr_esc_attr_x( $s, $c, $d = 'wordefinery' ) {
    return  esc_attr_x( $s, $c, $d );
}

function wr_esc_html_x( $s, $c, $d = 'wordefinery' ) {
    return  esc_html_x( $s, $c, $d );
}


if (!function_exists('_x')) {
    function _x( $t, $c, $d = 'default' ) {
        return _c( $t.'|'.$c, $d );
    }
}

if (!function_exists('_ex')) {
    function _ex( $t, $c, $d = 'default' ) {
        echo _c( $t.'|'.$c, $d );
    }
}

if (!function_exists('_nx')) {
    function _nx( $s, $p, $n, $c, $d = 'default' ) {
        return _nc( $s.'|'.$c, $p.'|'.$c, $n, $d );
    }
}

if (!function_exists('_nx_noop')) {
    function _nx_noop( $singular, $plural, $context ) {
        return array( 0 => $singular, 1 => $plural, 2 => $context, 'singular' => $singular, 'plural' => $plural, 'context' => $context );
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr( $text ) {
    	$safe_text = wp_check_invalid_utf8( $text );
    	$safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    	return apply_filters( 'attribute_escape', $safe_text, $text );
    }
}

if (!function_exists('esc_html')) {
    function esc_html( $text ) {
        $safe_text = wp_check_invalid_utf8( $text );
        $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
        return apply_filters( 'esc_html', $safe_text, $text );
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__( $t, $d = 'default' ) {
        return esc_attr( __( $t, $d ) );
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e( $t, $d = 'default' ) {
        echo esc_attr__( $t, $d );
    }
}

if (!function_exists('esc_attr_x')) {
    function esc_attr_x( $s, $c, $d = 'default' ) {
        return esc_attr( _x( $s, $c, $d ) );
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__( $t, $d = 'default' ) {
        return esc_html( __( $t, $d ) );
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e( $t, $d = 'default' ) {
        echo esc_html__( $t, $d );
    }
}

if (!function_exists('esc_html_x')) {
    function esc_html_x( $s, $c, $d = 'default' ) {
        return esc_html( _x( $s, $c, $d ) );
    }
}

if (!function_exists('_wp_specialchars')) {
    function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
    	$string = (string) $string;

    	if ( 0 === strlen( $string ) )
    		return '';

    	// Don't bother if there are no specialchars - saves some processing
    	if ( ! preg_match( '/[&<>"\']/', $string ) )
    		return $string;

    	// Account for the previous behaviour of the function when the $quote_style is not an accepted value
    	if ( empty( $quote_style ) )
    		$quote_style = ENT_NOQUOTES;
    	elseif ( ! in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) )
    		$quote_style = ENT_QUOTES;

    	// Store the site charset as a static to avoid multiple calls to wp_load_alloptions()
    	if ( ! $charset ) {
    		static $_charset;
    		if ( ! isset( $_charset ) ) {
    			$alloptions = wp_load_alloptions();
    			$_charset = isset( $alloptions['blog_charset'] ) ? $alloptions['blog_charset'] : '';
    		}
    		$charset = $_charset;
    	}

    	if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ) ) )
    		$charset = 'UTF-8';

    	$_quote_style = $quote_style;

    	if ( $quote_style === 'double' ) {
    		$quote_style = ENT_COMPAT;
    		$_quote_style = ENT_COMPAT;
    	} elseif ( $quote_style === 'single' ) {
    		$quote_style = ENT_NOQUOTES;
    	}

    	// Handle double encoding ourselves
    	if ( $double_encode ) {
    		$string = @htmlspecialchars( $string, $quote_style, $charset );
    	} else {
    		// Decode &amp; into &
    		$string = wp_specialchars_decode( $string, $_quote_style );

    		// Guarantee every &entity; is valid or re-encode the &
    		$string = wp_kses_normalize_entities( $string );

    		// Now re-encode everything except &entity;
    		$string = preg_split( '/(&#?x?[0-9a-z]+;)/i', $string, -1, PREG_SPLIT_DELIM_CAPTURE );

    		for ( $i = 0; $i < count( $string ); $i += 2 )
    			$string[$i] = @htmlspecialchars( $string[$i], $quote_style, $charset );

    		$string = implode( '', $string );
    	}

    	// Backwards compatibility
    	if ( 'single' === $_quote_style )
    		$string = str_replace( "'", '&#039;', $string );

    	return $string;
    }
}

if (!function_exists('wp_specialchars_decode')) {
    function wp_specialchars_decode( $string, $quote_style = ENT_NOQUOTES ) {
    	$string = (string) $string;

    	if ( 0 === strlen( $string ) ) {
    		return '';
    	}

    	// Don't bother if there are no entities - saves a lot of processing
    	if ( strpos( $string, '&' ) === false ) {
    		return $string;
    	}

    	// Match the previous behaviour of _wp_specialchars() when the $quote_style is not an accepted value
    	if ( empty( $quote_style ) ) {
    		$quote_style = ENT_NOQUOTES;
    	} elseif ( !in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) ) {
    		$quote_style = ENT_QUOTES;
    	}

    	// More complete than get_html_translation_table( HTML_SPECIALCHARS )
    	$single = array( '&#039;'  => '\'', '&#x27;' => '\'' );
    	$single_preg = array( '/&#0*39;/'  => '&#039;', '/&#x0*27;/i' => '&#x27;' );
    	$double = array( '&quot;' => '"', '&#034;'  => '"', '&#x22;' => '"' );
    	$double_preg = array( '/&#0*34;/'  => '&#034;', '/&#x0*22;/i' => '&#x22;' );
    	$others = array( '&lt;'   => '<', '&#060;'  => '<', '&gt;'   => '>', '&#062;'  => '>', '&amp;'  => '&', '&#038;'  => '&', '&#x26;' => '&' );
    	$others_preg = array( '/&#0*60;/'  => '&#060;', '/&#0*62;/'  => '&#062;', '/&#0*38;/'  => '&#038;', '/&#x0*26;/i' => '&#x26;' );

    	if ( $quote_style === ENT_QUOTES ) {
    		$translation = array_merge( $single, $double, $others );
    		$translation_preg = array_merge( $single_preg, $double_preg, $others_preg );
    	} elseif ( $quote_style === ENT_COMPAT || $quote_style === 'double' ) {
    		$translation = array_merge( $double, $others );
    		$translation_preg = array_merge( $double_preg, $others_preg );
    	} elseif ( $quote_style === 'single' ) {
    		$translation = array_merge( $single, $others );
    		$translation_preg = array_merge( $single_preg, $others_preg );
    	} elseif ( $quote_style === ENT_NOQUOTES ) {
    		$translation = $others;
    		$translation_preg = $others_preg;
    	}

    	// Remove zero padding on numeric entities
    	$string = preg_replace( array_keys( $translation_preg ), array_values( $translation_preg ), $string );

    	// Replace characters according to translation table
    	return strtr( $string, $translation );
    }
}

if (!function_exists('wp_check_invalid_utf8')) {
    function wp_check_invalid_utf8( $string, $strip = false ) {
    	$string = (string) $string;

    	if ( 0 === strlen( $string ) ) {
    		return '';
    	}

    	// Store the site charset as a static to avoid multiple calls to get_option()
    	static $is_utf8;
    	if ( !isset( $is_utf8 ) ) {
    		$is_utf8 = in_array( get_option( 'blog_charset' ), array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) );
    	}
    	if ( !$is_utf8 ) {
    		return $string;
    	}

    	// Check for support for utf8 in the installed PCRE library once and store the result in a static
    	static $utf8_pcre;
    	if ( !isset( $utf8_pcre ) ) {
    		$utf8_pcre = @preg_match( '/^./u', 'a' );
    	}
    	// We can't demand utf8 in the PCRE installation, so just return the string in those cases
    	if ( !$utf8_pcre ) {
    		return $string;
    	}

    	// preg_match fails when it encounters invalid UTF8 in $string
    	if ( 1 === @preg_match( '/^./us', $string ) ) {
    		return $string;
    	}

    	// Attempt to strip the bad chars if requested (not recommended)
    	if ( $strip && function_exists( 'iconv' ) ) {
    		return iconv( 'utf-8', 'utf-8', $string );
    	}

    	return '';
    }
}
