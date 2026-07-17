<?php

namespace App\Http\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Quiénes somos')]
class AboutPage extends Component
{
    public function render()
    {
        return view('livewire.storefront.about-page');
    }
}
