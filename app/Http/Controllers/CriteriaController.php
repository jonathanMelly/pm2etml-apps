<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DefaultCriteria;

use Illuminate\Support\Facades\Log;


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
      // Log des informations à propos de la requête et de l'utilisateur
      Log::info('Mise à jour des critères personnalisés', [
         'user_id' => auth()->id(),
         'criterias' => $request->input('criterias'),
      ]);

      // Récupérer les données des critères et l'ID de l'utilisateur
      $criteriasData = $request->input('criterias');
      $userId = auth()->id();

      // Enregistrer les critères de l'utilisateur
      DefaultCriteria::saveUserCriterias($criteriasData, $userId);

      // Log après avoir sauvegardé les critères
      Log::info('Critères personnalisés sauvegardés avec succès pour l\'utilisateur', [
         'user_id' => $userId,
         'criterias' => $criteriasData,
      ]);

      // Retourner avec un message de succès
      return redirect()->route('dashboard')->with('success', 'Critères personnalisés mis à jour avec succès !');
   }
}
