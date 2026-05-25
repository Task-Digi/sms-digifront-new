<?php

namespace App\Livewire;

use App\Models\Template;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TemplateManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?int $editingId = null;
    public ?string $sender_id = null;
    public ?string $slug = null;
    public string $name = '';
    public string $body = '';
    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'sender_id' => 'nullable|string|max:20',
            'slug'      => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9_\-]+$/i', Rule::unique('templates', 'slug')->ignore($this->editingId)],
            'name'      => 'required|string|max:100',
            'body'      => 'required|string|max:1000',
        ];
    }

    public function newTemplate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $tpl = Template::findOrFail($id);
        $this->editingId = $tpl->id;
        $this->sender_id = $tpl->sender_id;
        $this->slug      = $tpl->slug;
        $this->name      = $tpl->name;
        $this->body      = $tpl->body;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['sender_id'] = ($data['sender_id'] ?? '') === '' ? null : $data['sender_id'];
        $data['slug']      = ($data['slug'] ?? '') === '' ? null : $data['slug'];

        if ($this->editingId) {
            Template::find($this->editingId)->update($data);
            session()->flash('success', 'Template updated.');
        } else {
            Template::create($data);
            session()->flash('success', 'Template created.');
        }

        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Template::find($id)?->delete();
        session()->flash('success', 'Template deleted.');
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'sender_id', 'slug', 'name', 'body', 'showForm']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.template-management', [
            'templates' => Template::orderBy('sender_id')->orderBy('name')->paginate(25),
        ]);
    }
}
