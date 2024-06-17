<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EvaluationChanged extends Mailable
{
    use Queueable, SerializesModels;

    protected array $informations;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $informations)
    {
        $this->informations = $informations;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Marketplace : évaluations mises à jour')
            ->markdown(
                'emails.evaluation.changed',
                ['informations' => $this->informations]
            );
    }
}
