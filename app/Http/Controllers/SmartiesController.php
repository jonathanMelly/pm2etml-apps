<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SmartiesController extends Controller
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (! config('app.smarties_enabled')) {
            throw new \Exception('Smarties is not enabled');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Inertia::render('smarties/index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
