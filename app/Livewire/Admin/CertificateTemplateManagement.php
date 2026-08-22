<?php

namespace App\Livewire\Admin;

use App\Enums\CertificateScope;
use App\Models\CertificateTemplate;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CertificateTemplateManagement extends Component
{
    use WithFileUploads;

    public Event $event;

    // Form state
    public ?int $editingId = null;
    public string $name = '';
    public string $scope = 'fallback';
    public string $orientation = 'landscape';
    public $image = null; // TemporaryUpload
    public ?string $existingImagePath = null;

    // Posisi teks (persen 0-100)
    public float $name_x = 50, $name_y = 45, $name_font_size = 5;
    public float $category_x = 50, $category_y = 58, $category_font_size = 2.8;
    public float $status_x = 50, $status_y = 65, $status_font_size = 3.5;

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function render()
    {
        $templates = $this->event->certificateTemplates()->orderByDesc('id')->get();

        return view('livewire.admin.certificate-template-management', [
            'templates' => $templates,
            'scopes' => CertificateScope::class,
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $template = $this->event->certificateTemplates()->findOrFail($id);

        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->scope = $template->scope->value;
        $this->orientation = $template->orientation;
        $this->existingImagePath = $template->image_path;
        $this->image = null;
        foreach (['name_x', 'name_y', 'name_font_size', 'category_x', 'category_y',
                  'category_font_size', 'status_x', 'status_y', 'status_font_size'] as $field) {
            $this->{$field} = (float) $template->{$field};
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'scope' => ['required', 'in:' . collect(CertificateScope::cases())->pluck('value')->implode(',')],
            'orientation' => ['required', 'in:portrait,landscape'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'name_x' => ['numeric', 'between:0,100'],
            'name_y' => ['numeric', 'between:0,100'],
            'name_font_size' => ['numeric', 'between:0,100'],
            'category_x' => ['numeric', 'between:0,100'],
            'category_y' => ['numeric', 'between:0,100'],
            'category_font_size' => ['numeric', 'between:0,100'],
            'status_x' => ['numeric', 'between:0,100'],
            'status_y' => ['numeric', 'between:0,100'],
            'status_font_size' => ['numeric', 'between:0,100'],
        ]);

        if ($this->image) {
            $path = $this->image->store('certificate-templates', 'public');
        } else {
            $path = $this->existingImagePath;
        }

        abort_unless($path, 422, 'Gambar template wajib diunggah.');

        $data = collect($validated)->except('image')->merge(['image_path' => $path])->all();

        if ($this->editingId) {
            $template = $this->event->certificateTemplates()->findOrFail($this->editingId);
            $oldPath = $template->image_path;
            $template->update($data);
            // Hapus file lama jika diganti
            if ($this->image && $oldPath && $oldPath !== $path) {
                Storage::disk('public')->delete($oldPath);
            }
            session()->flash('certificate-templates-success', 'Template berhasil diperbarui.');
        } else {
            $this->event->certificateTemplates()->create($data);
            session()->flash('certificate-templates-success', 'Template berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $template = $this->event->certificateTemplates()->findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);
    }

    public function delete(int $id): void
    {
        $template = $this->event->certificateTemplates()->findOrFail($id);
        Storage::disk('public')->delete($template->image_path);
        $template->delete();
        if ($this->editingId === $id) {
            $this->resetForm();
        }
        session()->flash('certificate-templates-success', 'Template dihapus.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->scope = 'fallback';
        $this->orientation = 'landscape';
        $this->image = null;
        $this->existingImagePath = null;
        $this->name_x = 50; $this->name_y = 45; $this->name_font_size = 5;
        $this->category_x = 50; $this->category_y = 58; $this->category_font_size = 2.8;
        $this->status_x = 50; $this->status_y = 65; $this->status_font_size = 3.5;
    }
}
