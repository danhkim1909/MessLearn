<?php

namespace App\Routing;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

class CustomUrlGenerator extends BaseUrlGenerator
{
    /**
     * Get the URL to a named route.
     * Overridden to generate root-relative URLs by default for reverse proxies, tunnels, and hostings.
     *
     * @param  \BackedEnum|string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = false)
    {
        if (config('app.force_relative_urls', true)) {
            $absolute = false;
        }

        return parent::route($name, $parameters, $absolute);
    }

    /**
     * Generate the URL to an application asset.
     * Overridden to return root-relative asset paths unless external URL.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }

    /**
     * Generate an absolute or relative URL to the given path.
     * Overridden to return root-relative path when force_relative_urls is enabled.
     *
     * @param  string  $path
     * @param  mixed  $extra
     * @param  bool|null  $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        if (config('app.force_relative_urls', true)) {
            $tail = implode('/', array_map(
                'rawurlencode', (array) $this->formatParameters($extra))
            );

            [$path, $query] = $this->extractQueryString($path);

            $combined = trim(trim($path, '/') . '/' . trim($tail, '/'), '/');

            return '/' . $combined . $query;
        }

        return parent::to($path, $extra, $secure);
    }
}
