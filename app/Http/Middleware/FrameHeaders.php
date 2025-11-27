<?php
/**
 * ETML
 * Auteur      : Christopher Ristic
 * Date        : 23.11.2025
 * Description : Middleware ajoutant les en-têtes de sécurité empêchant
 *               l’intégration du site dans une iframe (anti-clickjacking).
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Classe FrameHeaders
 * Ajoute les headers X-Frame-Options et Content-Security-Policy
 * à toutes les réponses HTTP compatibles.
 */
class FrameHeaders
{
    /**
     * Exécute le middleware.
     *
     * @param Request $request Requête HTTP reçue
     * @param Closure $next    Prochaine étape du pipeline
     *
     * @return mixed Réponse HTTP enrichie des headers de sécurité
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Ajout des en-têtes anti-clickjacking
        if (method_exists($response, 'header')) {
            $response->header('X-Frame-Options', 'DENY');
            $response->header('Content-Security-Policy', "frame-ancestors 'none'");
        }

        return $response;
    }
}
