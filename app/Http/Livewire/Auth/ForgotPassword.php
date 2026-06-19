<?php

namespace App\Http\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Recuperar contraseña')]
class ForgotPassword extends Component
{
    public string $email = '';
    public bool $sent = false;

    public function sendLink(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        // Always call sendResetLink regardless of whether the email exists
        // to prevent user enumeration attacks
        Password::sendResetLink(['email' => $this->email]);

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
