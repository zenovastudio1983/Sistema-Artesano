<?php

namespace App\Http\Livewire\Sales;

use App\Domains\Sales\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cliente')]
class CustomerForm extends Component
{
    public ?int $customerId = null;

    public string $business_name = '';
    public string $trade_name = '';
    public string $tax_id = '';
    public string $tax_type = 'ruc';
    public string $customer_type = 'retail';
    public string $email = '';
    public string $phone = '';
    public string $mobile = '';
    public string $address = '';
    public string $city = '';
    public string $country = 'PE';
    public string $contact_name = '';
    public string $payment_days = '0';
    public string $credit_limit = '0';
    public string $discount_percent = '0';
    public string $price_list = 'standard';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(?Customer $customer = null): void
    {
        if ($customer && $customer->exists) {
            $this->customerId      = $customer->id;
            $this->business_name   = $customer->business_name;
            $this->trade_name      = $customer->trade_name ?? '';
            $this->tax_id          = $customer->tax_id ?? '';
            $this->tax_type        = $customer->tax_type ?? 'ruc';
            $this->customer_type   = $customer->customer_type ?? 'retail';
            $this->email           = $customer->email ?? '';
            $this->phone           = $customer->phone ?? '';
            $this->mobile          = $customer->mobile ?? '';
            $this->address         = $customer->address ?? '';
            $this->city            = $customer->city ?? '';
            $this->country         = $customer->country ?? 'PE';
            $this->contact_name    = $customer->contact_name ?? '';
            $this->payment_days    = (string) ($customer->payment_days ?? 0);
            $this->credit_limit    = (string) ($customer->credit_limit ?? 0);
            $this->discount_percent = (string) ($customer->discount_percent ?? 0);
            $this->price_list      = $customer->price_list ?? 'standard';
            $this->notes           = $customer->notes ?? '';
            $this->is_active       = $customer->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'business_name'    => 'required|string|max:200',
            'trade_name'       => 'nullable|string|max:200',
            'tax_id'           => 'nullable|string|max:20',
            'customer_type'    => 'required|in:retail,wholesale,distributor',
            'email'            => 'nullable|email|max:150',
            'phone'            => 'nullable|string|max:30',
            'city'             => 'nullable|string|max:100',
            'contact_name'     => 'nullable|string|max:150',
            'payment_days'     => 'nullable|integer|min:0|max:365',
            'credit_limit'     => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'price_list'       => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:1000',
            'is_active'        => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'business_name'    => $this->business_name,
            'trade_name'       => $this->trade_name ?: null,
            'tax_id'           => $this->tax_id ?: null,
            'tax_type'         => $this->tax_type ?: null,
            'customer_type'    => $this->customer_type,
            'email'            => $this->email ?: null,
            'phone'            => $this->phone ?: null,
            'mobile'           => $this->mobile ?: null,
            'address'          => $this->address ?: null,
            'city'             => $this->city ?: null,
            'country'          => $this->country,
            'contact_name'     => $this->contact_name ?: null,
            'payment_days'     => (int) $this->payment_days,
            'credit_limit'     => (float) $this->credit_limit,
            'discount_percent' => (float) $this->discount_percent,
            'price_list'       => $this->price_list ?: 'standard',
            'notes'            => $this->notes ?: null,
            'is_active'        => $this->is_active,
        ];

        if ($this->customerId) {
            Customer::findOrFail($this->customerId)->update($data);
            session()->flash('success', 'Cliente actualizado.');
        } else {
            Customer::create($data);
            session()->flash('success', 'Cliente creado.');
        }

        $this->redirect(route('customers.index'));
    }

    public function render()
    {
        return view('livewire.sales.customer-form');
    }
}
