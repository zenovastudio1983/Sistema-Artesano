<?php

namespace App\Http\Livewire\Users;

use App\Domains\Users\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Usuario')]
class UserForm extends Component
{
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public bool $is_active = true;
    public array $selectedRoles = [];

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->is_active = $user->is_active;
            $this->selectedRoles = $user->roles->pluck('name')->toArray();
        }
    }

    protected function rules(): array
    {
        $passwordRule = $this->userId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed';

        return [
            'name'                 => 'required|string|max:100',
            'email'                => 'required|email|unique:users,email' . ($this->userId ? ",{$this->userId}" : ''),
            'password'             => $passwordRule,
            'passwordConfirmation' => 'same:password',
            'is_active'            => 'boolean',
        ];
    }

    protected $validationAttributes = [
        'passwordConfirmation' => 'confirmación de contraseña',
    ];

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            $message = 'Usuario actualizado correctamente.';
        } else {
            $user = User::create($data);
            $message = 'Usuario creado correctamente.';
        }

        $user->syncRoles($this->selectedRoles);

        session()->flash('success', $message);
        $this->redirect(route('users.index'));
    }

    public function render()
    {
        return view('livewire.users.user-form', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}
