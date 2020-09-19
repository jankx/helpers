<?php
if (!function_exists('array_get')) {
    function array_get($array, $key, $defaultValue = false)
    {
        $keys = explode('.', $key);
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                return $defaultValue;
            }
            $value = $array = $array[$key];
        }
        return $value;
    }
}

if (!function_exists('jankx_is_mobile_template')) {
    function jankx_is_mobile_template()
    {
        if (isset($_COOKIE['view'])) {
            $isMobile = array_get($_COOKIE, 'view', 'desktop') === 'mobile';
        } else {
            $isMobile = wp_is_mobile();
        }

        return apply_filters(
            'jankx_is_mobile_template',
            $isMobile
        );
    }
}

if (!function_exists('jankx_generate_html_attributes')) {
    function jankx_generate_html_attributes($attributes)
    {
        if (!is_array($attributes)) {
            return '';
        }
        $attributesStr = '';
        foreach ($attributes as $attribute => $value) {
            $attributesStr .= sprintf('%s="%s" ', $attribute, $value);
        }
        return rtrim($attributesStr);
    }
}

if (!function_exists('jankx_get_wp_image_sizes')) {
    function jankx_get_wp_image_sizes($size)
    {
        if (in_array($size, array( 'thumbnail', 'medium', 'large' ))) {
            return array(
                'width'  => get_option($size . '_size_w'),
                'height' => get_option($size . '_size_h'),
            );
        }

        $get_intermediate_image_sizes = get_intermediate_image_sizes();
        if (! $size || ! in_array($size, $get_intermediate_image_sizes)) {
            return false;
        }
        // Get additional image sizes;
        $wp_additional_image_sizes = wp_get_additional_image_sizes();

        return $wp_additional_image_sizes[ $size ];
    }
}

if (!function_exists('jankx_get_image_numeric_size')) {
    function jankx_get_image_numeric_size($textSize)
    {
        if (empty($textSize)) {
            return false;
        }

        $height = 0;
        $width  = 0;
        if (is_array($textSize)) {
            $width  = array_get($textSize, 0);
            $height = array_get($textSize, 1);

            return array(
                'width'  => $width,
                'height' => $height,
            );
        }

        return jankx_get_wp_image_sizes($textSize);
    }
}

if (!function_exists('jankx_placeholder_image')) {
    function jankx_placeholder_image($imageSize, $placeholder = '')
    {
        $imageSize = jankx_get_image_numeric_size($imageSize);
        $siteName  = urlencode(get_bloginfo('name'));

        return sprintf(
            '<img src="https://placeholder.pics/svg/%1$s/FF7247-FF3876/FFFFFF/%2$s" alt="%3$s" />',
            implode('x', array_values($imageSize)),
            strtoupper($siteName),
            $$placeholder,
        );
    }
}

if (!function_exists('jankx_the_post_thumbnail')) {
    function jankx_the_post_thumbnail($size = 'thumbnail', $attr = array(), $post = null)
    {
        if (has_post_thumbnail($post)) {
            echo get_the_post_thumbnail($post, $size, $attr);
        } else {
            echo jankx_placeholder_image($size);
        }
    }
}
