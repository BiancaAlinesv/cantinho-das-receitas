<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        return response()->view('sitemap', ['receitas' => Receita::query()->publicadas()->latest('updated_at')->get()])->header('Content-Type', 'application/xml');
    }
}
