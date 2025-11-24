<?php

use Illuminate\Support\Facades\Route;
use App\Models\Evaluation;

Route::get('/debug-eval/{id}', function ($id) {
    $evaluation = Evaluation::with(['student', 'teacher', 'versions.creator'])->find($id);
    
    if (!$evaluation) return 'Evaluation not found';
    
    $output = "<h1>Evaluation #{$evaluation->id}</h1>";
    $output .= "<p>Student: {$evaluation->student->email} (ID: {$evaluation->student->id})</p>";
    $output .= "<p>Teacher: {$evaluation->teacher->email} (ID: {$evaluation->teacher->id})</p>";
    $output .= "<h2>Versions</h2><ul>";
    
    foreach ($evaluation->versions as $v) {
        $output .= "<li>ID: {$v->id} | Type: <strong>{$v->evaluator_type}</strong> | Creator: {$v->creator->email} (ID: {$v->created_by_user_id}) | Version #: {$v->version_number}</li>";
    }
    $output .= "</ul>";
    
    return $output;
});
