<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssessmentCriterionTemplate;

class AssessmentTemplateController extends Controller
{
   public function create()
   {
      $userId = auth()->id();
      $criteria = AssessmentCriterionTemplate::getUserCriteria($userId);

      if ($criteria->isEmpty()) {
         $criteria = AssessmentCriterionTemplate::getDefaultCriteria();
      }
      return view('fullevaluation-criteria-create', compact('criteria'));
   }

   public function update(Request $request)
   {
      $criteriasData = $request->input('criteria');
      $userId = auth()->id();

      AssessmentCriterionTemplate::saveUserCriteria($criteriasData, $userId);

      return redirect()->route('dashboard')
         ->with('success', __("Custom criteria updated successfully"));
   }
}
