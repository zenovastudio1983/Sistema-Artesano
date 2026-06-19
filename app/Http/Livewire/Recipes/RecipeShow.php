<?php

namespace App\Http\Livewire\Recipes;

use App\Domains\Recipes\Models\Recipe;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Receta')]
class RecipeShow extends Component
{
    public Recipe $recipe;

    public function mount(Recipe $recipe): void
    {
        $this->recipe = $recipe->load(['product.category', 'ingredients.product', 'additionalCosts']);
    }

    public function render()
    {
        return view('livewire.recipes.recipe-show');
    }
}
