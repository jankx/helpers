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
        return apply_filters(
            'jankx_is_mobile_template',
            wp_is_mobile()
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
