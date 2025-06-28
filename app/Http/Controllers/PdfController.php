<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
   public function saveFilledPdf(Request $request)
   {
      $data = $request->validate([
         'pdf' => 'required|string',
         'filename' => 'required|string',
      ]);

      $pdfData = base64_decode($data['pdf']);
      $filename = $data['filename'];

      // Définir le chemin où le PDF sera sauvegardé
      $path = 'pdfs/' . $filename;

      // Sauvegarder le PDF dans le répertoire de stockage
      Storage::disk('public')->put($path, $pdfData);

      return response()->json([
         'success' => true,
         'path' => $path
      ]);
   }
}
