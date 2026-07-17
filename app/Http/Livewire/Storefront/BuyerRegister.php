<?php

namespace App\Http\Livewire\Storefront;

use App\Domains\Storefront\Models\Buyer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Crear cuenta')]
class BuyerRegister extends Component
{
    public string $name     = '';
    public string $email    = '';
    public string $phone    = '';
    public string $password = '';
    public string $passwordConfirmation = '';

    public function register(): void
    {
        $this->validate([
            'name'                 => 'required|string|max:120',
            'email'                => 'required|email|unique:buyers,email',
            'phone'                => 'nullable|string|max:30',
            'password'             => 'required|string|min:8|confirmed',
            'passwordConfirmation' => 'required',
        ], [
            'email.unique'      => 'Ya existe una cuenta con ese correo.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'=> 'Las contraseñas no coinciden.',
        ]);

        $buyer = Buyer::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone ?: null,
            'password' => $this->password,
        ]);

        auth('buyer')->login($buyer, true);

        $this->redirect(route('tienda.index'));
    }

    public function render()
    {
        return view('livewire.storefront.buyer-register');
    }
}
