<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?int $editingId = null;
    public string $name = '';
    public string $mobile = '';
    public string $sender_id = '';
    public bool $is_active = true;
    public bool $is_admin = false;
    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|max:100',
            'mobile'    => ['required', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($this->editingId)],
            'sender_id' => 'required|string|max:20',
            'is_active' => 'boolean',
            'is_admin'  => 'boolean',
        ];
    }

    public function newUser(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name      = $user->name;
        $this->mobile    = $user->mobile;
        $this->sender_id = $user->sender_id;
        $this->is_active = (bool) $user->is_active;
        $this->is_admin  = (bool) $user->is_admin;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // Prevent the signed-in admin from demoting themselves and getting locked out.
        $sessionUserId = (int) (session('user')['id'] ?? 0);
        if ($this->editingId && $this->editingId === $sessionUserId && empty($data['is_admin'])) {
            session()->flash('error', 'You cannot remove admin from the user you are signed in as.');
            return;
        }

        if ($this->editingId) {
            User::find($this->editingId)->update($data);
            session()->flash('success', 'User updated.');
        } else {
            User::create($data);
            session()->flash('success', 'User created.');
        }

        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        if ((int) (session('user')['id'] ?? 0) === $id) {
            session()->flash('error', 'You cannot delete the user you are signed in as.');
            return;
        }

        User::find($id)?->delete();
        session()->flash('success', 'User deleted.');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'mobile', 'sender_id', 'is_admin', 'showForm']);
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.user-management', [
            'users' => User::orderBy('id')->paginate(25),
        ]);
    }
}
