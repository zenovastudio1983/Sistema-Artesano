<?php

namespace App\Http\Livewire\Recipes;

use App\Domains\Products\Models\Product;
use App\Domains\Recipes\Models\Recipe;
use App\Domains\Recipes\Models\RecipeIngredient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Receta')]
class RecipeForm extends Component
{
    public ?int $recipeId = null;

    public int $productId = 0;
    public string $name = '';
    public string $description = '';
    public string $version = '1.0';
    public string $yieldQuantity = '';
    public string $yieldUnit = '';
    public int $productionTimeMinutes = 0;
    public string $laborCost = '0';
    public string $overheadCost = '0';
    public string $instructions = '';
    public string $notes = '';
    public bool $is_active = true;
    public bool $is_default = false;

    public array $ingredients = [];

    public float $materialCost = 0;
    public float $totalCost = 0;
    public float $unitCost = 0;

    public function mount(?Recipe $recipe = null): void
    {
        if ($recipe && $recipe->exists) {
            $this->recipeId               = $recipe->id;
            $this->productId              = $recipe->product_id;
            $this->name                   = $recipe->name;
            $this->description            = $recipe->description ?? '';
            $this->version                = $recipe->version ?? '1.0';
            $this->yieldQuantity          = (string) $recipe->yield_quantity;
            $this->yieldUnit              = $recipe->yield_unit ?? '';
            $this->productionTimeMinutes  = $recipe->production_time_minutes ?? 0;
            $this->laborCost              = (string) ($recipe->labor_cost ?? 0);
            $this->overheadCost           = (string) ($recipe->overhead_cost ?? 0);
            $this->instructions           = $recipe->instructions ?? '';
            $this->notes                  = $recipe->notes ?? '';
            $this->is_active              = $recipe->is_active;
            $this->is_default             = $recipe->is_default;

            foreach ($recipe->ingredients->load('product') as $ing) {
                $this->ingredients[] = [
                    'product_id'       => $ing->product_id,
                    'product_name'     => $ing->product->name ?? '',
                    'quantity'         => (float) $ing->quantity,
                    'unit'             => $ing->unit ?? '',
                    'scrap_percentage' => (float) ($ing->scrap_percentage ?? 0),
                    'unit_cost'        => (float) ($ing->unit_cost ?? 0),
                    'is_optional'      => (bool) ($ing->is_optional ?? false),
                    'notes'            => $ing->notes ?? '',
                    'total_cost'       => round((float) $ing->quantity * (1 + ($ing->scrap_percentage ?? 0) / 100) * (float) ($ing->unit_cost ?? 0), 4),
                ];
            }
        }

        if (empty($this->ingredients)) {
            $this->addIngredient();
        }

        $this->recalculate();
    }

    public function addIngredient(): void
    {
        $this->ingredients[] = [
            'product_id' => 0, 'product_name' => '', 'quantity' => 0,
            'unit' => '', 'scrap_percentage' => 0, 'unit_cost' => 0,
            'is_optional' => false, 'notes' => '', 'total_cost' => 0,
        ];
    }

    public function removeIngredient(int $index): void
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
        $this->recalculate();
    }

    public function updatedIngredients(mixed $value, string $key): void
    {
        [$index, $field] = explode('.', $key, 2);

        if ($field === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->ingredients[$index]['product_name'] = $product->name;
                $this->ingredients[$index]['unit'] = $product->unit ?? '';
                $this->ingredients[$index]['unit_cost'] = (float) ($product->average_cost ?? $product->cost ?? 0);
            }
        }

        if (in_array($field, ['quantity', 'unit_cost', 'scrap_percentage'])) {
            $q = (float) ($this->ingredients[$index]['quantity'] ?? 0);
            $c = (float) ($this->ingredients[$index]['unit_cost'] ?? 0);
            $s = (float) ($this->ingredients[$index]['scrap_percentage'] ?? 0);
            $this->ingredients[$index]['total_cost'] = round($q * (1 + $s / 100) * $c, 4);
        }

        $this->recalculate();
    }

    private function recalculate(): void
    {
        $this->materialCost = round(collect($this->ingredients)->sum('total_cost'), 4);
        $this->totalCost    = $this->materialCost + (float) $this->laborCost + (float) $this->overheadCost;
        $yield = (float) $this->yieldQuantity;
        $this->unitCost = $yield > 0 ? round($this->totalCost / $yield, 4) : 0;
    }

    public function updatedLaborCost(): void { $this->recalculate(); }
    public function updatedOverheadCost(): void { $this->recalculate(); }
    public function updatedYieldQuantity(): void { $this->recalculate(); }

    public function save(): void
    {
        $this->validate([
            'productId'    => 'required|exists:products,id',
            'name'         => 'required|string|max:200',
            'yieldQuantity' => 'required|numeric|min:0.0001',
            'ingredients'  => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity'   => 'required|numeric|min:0.0001',
        ]);

        $this->recalculate();

        $data = [
            'product_id'               => $this->productId,
            'name'                     => $this->name,
            'description'              => $this->description ?: null,
            'version'                  => $this->version,
            'yield_quantity'           => (float) $this->yieldQuantity,
            'yield_unit'               => $this->yieldUnit ?: null,
            'production_time_minutes'  => $this->productionTimeMinutes,
            'labor_cost'               => (float) $this->laborCost,
            'overhead_cost'            => (float) $this->overheadCost,
            'material_cost'            => $this->materialCost,
            'total_cost'               => $this->totalCost,
            'unit_cost'                => $this->unitCost,
            'instructions'             => $this->instructions ?: null,
            'notes'                    => $this->notes ?: null,
            'is_active'                => $this->is_active,
            'is_default'               => $this->is_default,
        ];

        if ($this->recipeId) {
            $recipe = Recipe::findOrFail($this->recipeId);
            $recipe->update($data);
            $recipe->ingredients()->delete();
            $message = 'Receta actualizada.';
        } else {
            $recipe = Recipe::create($data);
            $message = 'Receta creada.';
        }

        foreach ($this->ingredients as $i => $ing) {
            RecipeIngredient::create([
                'recipe_id'        => $recipe->id,
                'product_id'       => $ing['product_id'],
                'quantity'         => (float) $ing['quantity'],
                'unit'             => $ing['unit'] ?: null,
                'scrap_percentage' => (float) ($ing['scrap_percentage'] ?? 0),
                'unit_cost'        => (float) ($ing['unit_cost'] ?? 0),
                'total_cost'       => (float) ($ing['total_cost'] ?? 0),
                'is_optional'      => (bool) ($ing['is_optional'] ?? false),
                'notes'            => $ing['notes'] ?: null,
                'sort_order'       => $i + 1,
            ]);
        }

        session()->flash('success', $message);
        $this->redirect(route('recipes.show', $recipe));
    }

    public function render()
    {
        return view('livewire.recipes.recipe-form', [
            'finishedProducts' => Product::where('status', 'active')->where('is_producible', true)->orderBy('name')->get(),
            'allProducts'      => Product::where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}
