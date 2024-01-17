<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class emailAberturaRestricoes extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $docente;
    public $ano;
    public $anoProximo;
    public $semestre;
    public $ucs;
    public $ucsResp;
    public $withUcs;
    public $link;
    public $appName;
    public function __construct($docente,$periodo,$ucsResp,$ucs)
    {
        //
        $this->docente=$docente->nome;
        $this->ano=$periodo->ano;
        $this->anoProximo = is_numeric($periodo->ano) ? (int)$periodo->ano + 1 : null;
        $this->semestre=$periodo->semestre;
        $this->ucs=$ucs;
        $this->ucsResp=$ucsResp;
        $this->withUcs = $ucs ? " e os formulários de Restrições de Unidades Curriculares" : "";
        $this->link=env('APP_URL')."/restricoes";
        $this->appname=env('APP_NAME');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Formulário de Restrições Disponivel - SCH - ESTGA - UA',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.emailAberturaRestricoes',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    public function ucsRespList(): string
    {
        $list = "";
        if ($this->ucsResp != null) {
            $list .= "<p>&emsp;Foi lhe atribuído as Unidades Curriculares, como docente responsável:</p><ul>";
            foreach ($this->ucsResp as $uc) {
                $list .= "<li>" . $uc->codigo . " - " . $uc->nome . "</li>";
            }
            $list .= "</ul><br>";
        }

        return $list;
    }
    public function ucsList(): string
    {
        $list = "";
        if ($this->ucs != null) {
            if ($this->ucsResp != null) {
                $list .= "<p>&emsp;E como docente auxiliar:</p><ul>";
            } else {
                $list .= "<p>&emsp;Foi lhe atribuído as Unidades Curriculares, como docente auxiliar:</p><ul>";
            }
            foreach ($this->ucs as $uc) {
                $list .= "<li>" . $uc->codigo . " - " . $uc->nome . "</li>";
            }
            $list .= "</ul><br>";
        }

        return $list;
    }
}
