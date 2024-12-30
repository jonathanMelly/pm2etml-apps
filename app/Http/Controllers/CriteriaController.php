<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DefaultCriteria;

class CriteriaController extends Controller
{
   public function create()
   {
      $userId = auth()->id();
      $criterias = DefaultCriteria::getUserCriterias($userId);

      if ($criterias->isEmpty()) {
         $criterias = DefaultCriteria::getDefaultCriterias();
      }

      return view('create-customCriterias', compact('criterias'));
   }

   public function update(Request $request)
   {
      $criteriasData = $request->input('criterias');
      $userId = auth()->id();

      DefaultCriteria::saveUserCriterias($criteriasData, $userId);

      return redirect()->route('dashboard')->with('success', 'Critères personnalisés mis à jour avec succès !');
   }
}
