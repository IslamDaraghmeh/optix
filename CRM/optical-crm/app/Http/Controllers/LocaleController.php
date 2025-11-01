<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        // Validate the locale
        if (!in_array($locale, ['en', 'ar'])) {
            abort(400, 'Invalid locale');
        }

        // Set the locale
        App::setLocale($locale);
        Session::put('locale', $locale);

        // Redirect back to the previous page
        return redirect()->back();
    }
}
