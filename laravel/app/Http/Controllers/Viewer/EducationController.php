<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use App\Models\EducationArticle;
use Illuminate\Support\Collection;

class EducationController extends Controller
{
    public function index()
    {
        $articles = collect();

        try {
            $articles = EducationArticle::published()
                ->latest('published_at')
                ->get();
        } catch (\Exception $e) {
            // DB not yet available — page renders with empty article list
            // Run: php artisan migrate && php artisan db:seed
        }

        return view('viewer.education.index', compact('articles'));
    }
}
