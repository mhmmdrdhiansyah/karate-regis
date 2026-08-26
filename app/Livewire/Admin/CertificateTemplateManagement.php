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

    // Daftar blok teks fleksibel: [{content, x, y, font_size, bold}] (persen 0-100)
    public array $texts = [];

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
        $this->texts = array_map(
            fn (array $t) => [
                'content' => $t['content'],
                'x' => (float) $t['x'],
                'y' => (float) $t['y'],
                'font_size' => (float) $t['font_size'],
                'bold' => (bool) ($t['bold'] ?? false),
                'font_family' => $t['font_family'] ?? 'times',
                'color' => $t['color'] ?? '#000000',
            ],
            $template->texts,
        );
    }

    public function addText(): void
    {
        $this->texts[] = ['content' => '{nama}', 'x' => 50, 'y' => 50, 'font_size' => 3, 'bold' => false, 'font_family' => 'times', 'color' => '#000000'];
    }

    public function removeText(int $index): void
    {
        unset($this->texts[$index]);
        $this->texts = array_values($this->texts);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'scope' => ['required', 'in:' . collect(CertificateScope::cases())->pluck('value')->implode(',')],
            'orientation' => ['required', 'in:portrait,landscape'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'texts' => ['required', 'array', 'min:1'],
            'texts.*.content' => ['required', 'string', 'max:255'],
            'texts.*.x' => ['numeric', 'between:0,100'],
            'texts.*.y' => ['numeric', 'between:0,100'],
            'texts.*.font_size' => ['numeric', 'between:0,100'],
            'texts.*.bold' => ['boolean'],
            'texts.*.font_family' => ['nullable', 'in:times,helvetica,arial,courier,greatvibes,dancingscript,caveat'],
            'texts.*.color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
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
        $this->dispatch('close-modal');
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
        $this->texts = CertificateTemplate::DEFAULT_TEXTS;
    }
}
