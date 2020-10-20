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

if (!function_exists('jankx_get_device_detector')) {
    function jankx_get_device_detector()
    {
        global $detector;
        if (is_null($detector)) {
            // Init Mobile Detect Library 2.8.34
            $detector = new Mobile_Detect();
        }
        if (class_exists(Jankx::class)) {
            // Create Jankx::device() method
            $jankxInstance = Jankx::instance();
            $jankxInstance->device = function () use ($detector) {
                return $detector;
            };
        }

        return $detector;
    }
}

if (!function_exists('jankx_is_mobile')) {
    function jankx_is_mobile()
    {
        return jankx_get_device_detector()->isMobile();
    }
}

if (!function_exists('jankx_is_mobile_template')) {
    function jankx_is_mobile_template()
    {
        if (isset($_COOKIE['view'])) {
            $isMobile = array_get($_COOKIE, 'view', 'desktop') === 'mobile';
        } else {
            $isMobile = jankx_is_mobile();
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
            $attributesStr .= sprintf(
                '%s="%s" ',
                $attribute,
                is_array($value) ? implode(' ', $value) : $value
            );
        }
        return rtrim($attributesStr);
    }
}

if (!function_exists('jankx_get_wp_image_sizes')) {
    function jankx_get_wp_image_sizes($size)
    {
        if (in_array($size, array( 'thumbnail', 'medium', 'large', 'medium_large' ))) {
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
        if (empty($imageSize) && in_array($imageSize, array('medium_large'))) {
            $imageSize = jankx_get_image_numeric_size('medium');
        } else {
            $imageSize = array(150, 150);
        }
        $siteName = urlencode(get_bloginfo('name'));
        return call_user_func_array('sprintf', apply_filters(
            'jankx_placeholder_image_args',
            array(
                '<img src="https://placeskull.com/%1$s/%2$s/%3$d" alt="%4$s" />',
                implode('/', array_values($imageSize)),
                '4a90e2',
                40,
                urldecode($siteName)
            )
        ));
    }
}

if (!function_exists('jankx_get_post_thumbnail')) {
    function jankx_get_post_thumbnail($size = 'thumbnail', $attr = array(), $post = null)
    {
        if (has_post_thumbnail($post)) {
            return get_the_post_thumbnail($post, $size, $attr);
        } else {
            return jankx_placeholder_image($size);
        }
    }
}

if (!function_exists('jankx_the_post_thumbnail')) {
    function jankx_the_post_thumbnail($size = 'thumbnail', $attr = array(), $post = null)
    {
        echo jankx_get_post_thumbnail($size, $attr, $post);
    }
}

if (!function_exists('jankx_template_has_footer')) {
    function jankx_template_has_footer()
    {
        return apply_filters('jankx_template_has_footer', true);
    }
}
