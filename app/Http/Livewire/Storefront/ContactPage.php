<?php

namespace App\Http\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Contacto')]
class ContactPage extends Component
{
    public string $name    = '';
    public string $email   = '';
    public string $message = '';
    public bool   $sent    = false;

    public function send(): void
    {
        $this->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email',
            'message' => 'required|string|min:10',
        ]);

        $wa      = config('erp.whatsapp_number', '');
        $text    = "📩 Nuevo mensaje de contacto\n\n";
        $text   .= "Nombre: {$this->name}\n";
        $text   .= "Email: {$this->email}\n\n";
        $text   .= "Mensaje:\n{$this->message}";

        $this->sent = true;
        $this->reset(['name', 'email', 'message']);

        $this->dispatch('open-whatsapp', url: 'https://wa.me/' . $wa . '?text=' . rawurlencode($text));
    }

    public function render()
    {
        return view('livewire.storefront.contact-page');
    }
}
