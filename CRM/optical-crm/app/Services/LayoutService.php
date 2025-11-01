<?php

namespace App\Services;

class LayoutService
{
    public static function getLayout($request = null)
    {
        $request = $request ?? request();
        $layout = $request->query('layout', session('layout', 'navbar'));

        // Save layout preference to session
        if ($request->has('layout')) {
            session(['layout' => $layout]);
        }

        return $layout;
    }

    public static function getLayoutView($request = null)
    {
        $layout = self::getLayout($request);

        return $layout === 'sidebar' ? 'layouts.app-sidebar' : 'layouts.app-navbar';
    }

    public static function isSidebarLayout($request = null)
    {
        return self::getLayout($request) === 'sidebar';
    }

    public static function isNavbarLayout($request = null)
    {
        return self::getLayout($request) === 'navbar';
    }
}
