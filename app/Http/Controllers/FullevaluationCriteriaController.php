<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DefaultCriteria;

class FullevaluationCriteriaController extends Controller
{
   public function create()
   {
      $userId = auth()->id();
      $criteria = DefaultCriteria::getUserCriterias($userId);

      if ($criteria->isEmpty()) {
         $criteria = DefaultCriteria::getDefaultCriteria();
      }
      return view('fullevaluation-criteria-create', compact('criteria'));
   }

   public function update(Request $request)
   {
      $criteriasData = $request->input('criteria');
      $userId = auth()->id();

      DefaultCriteria::saveUserCriteria($criteriasData, $userId);

      return redirect()->route('dashboard')
         ->with('success', __("Custom criteria updated successfully"));
   }
}
