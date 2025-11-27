<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

class BrowserKitTestCase extends \Laravel\BrowserKitTesting\TestCase
{
    use RefreshDatabase, TestHarness;

    public $baseUrl = 'http://localhost';

    /**
     * Attendre qu'une condition soit remplie
     *
     * @param callable $condition La condition à vérifier (doit retourner true/false)
     * @param int $timeout Timeout en secondes (défaut: 5)
     * @param int $interval Intervalle entre les vérifications en millisecondes (défaut: 100)
     * @return bool True si la condition est remplie, false si timeout
     */
    protected function waitFor(callable $condition, int $timeout = 5, int $interval = 100): bool
    {
        $startTime = microtime(true);
        $timeoutInSeconds = $timeout;

        while (microtime(true) - $startTime < $timeoutInSeconds) {
            if ($condition()) {
                return true;
            }
            usleep($interval * 1000); // Convertir ms en microseconds
        }

        return false;
    }
}
