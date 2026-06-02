<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GeneratedPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $generatedPassword;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $generatedPassword)
    {
        $this->user = $user;
        $this->generatedPassword = $generatedPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.generated_password')
            ->from('msuiit.emr@gmail.com')
            ->with([
                'user' => $this->user,
                'generatedPassword' => $this->generatedPassword
            ]);
    }
}
