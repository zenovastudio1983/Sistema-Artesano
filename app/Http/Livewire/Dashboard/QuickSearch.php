<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;

class QuickSearch extends Component
{
    public string $query = '';

    public array $results = [];

    public bool $open = false;

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            $this->open = false;
            return;
        }

        $this->open = true;
        $this->results = [];
    }

    public function render()
    {
        return view('livewire.dashboard.quick-search');
    }
}
