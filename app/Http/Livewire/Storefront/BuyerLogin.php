<?php

namespace App\Http\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Iniciar sesión')]
class BuyerLogin extends Component
{
    public string $email    = '';
    public string $password = '';
    public bool   $remember = false;

    public function login(): void
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! auth('buyer')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Correo o contraseña incorrectos.');
            return;
        }

        session()->regenerate();
        $this->redirect(route('tienda.index'));
    }

    public function render()
    {
        return view('livewire.storefront.buyer-login');
    }
}
