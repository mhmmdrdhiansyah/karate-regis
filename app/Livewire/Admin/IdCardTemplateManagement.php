<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\IdCardTemplate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class IdCardTemplateManagement extends Component
{
    use WithFileUploads;

    public Event $event;

    // Form state
    public ?int $editingId = null;
    public string $name = '';
    public $image = null; // TemporaryUpload
    public ?string $existingImagePath = null;

    // Daftar blok teks fleksibel: [{content, x, y, font_size, bold}] (persen 0-100)
    public array $texts = [];

    // Kotak foto pada kartu: persen terhadap ukuran kartu
    public array $photoBox = ['x' => 15, 'y' => 50, 'w' => 22, 'h' => 40];

    public function mount(Event $event): void
    {
        $this->event = $event;
        $this->resetForm();
    }

    public function render()
    {
        $templates = $this->event->idCardTemplates()->orderByDesc('id')->get();

        return view('livewire.admin.id-card-template-management', [
            'templates' => $templates,
            'defaultTexts' => IdCardTemplate::DEFAULT_TEXTS,
        ]);
    }

    public function getPreviewRatioProperty(): string
    {
        // Orientasi preview ikut gambar: upload baru cek temporary, edit cek file tersimpan
        if ($this->image) {
            $info = @getimagesize($this->image->getRealPath());
        } elseif ($this->existingImagePath) {
            $full = Storage::disk('public')->path($this->existingImagePath);
            $info = file_exists($full) ? @getimagesize($full) : false;
        } else {
            $info = false;
        }

        return $info ? ($info[1] > $info[0] ? '54/85.6' : '85.6/54') : '85.6/54';
    }

    public function create(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $template = $this->event->idCardTemplates()->findOrFail($id);

        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->existingImagePath = $template->image_path;
        $this->image = null;
        $this->texts = array_map(
            fn (array $text) => [
                'content' => $text['content'],
                'x' => (float) $text['x'],
                'y' => (float) $text['y'],
                'font_size' => (float) $text['font_size'],
                'bold' => (bool) ($text['bold'] ?? false),
                'font_family' => $text['font_family'] ?? 'helvetica',
                'color' => $text['color'] ?? '#000000',
            ],
            $template->texts,
        );
        $this->photoBox = $template->photo_box ?? IdCardTemplate::DEFAULT_PHOTO_BOX;
    }

    public function addText(): void
    {
        $this->texts[] = [
            'content' => '{nama}',
            'x' => 50,
            'y' => 50,
            'font_size' => 4,
            'bold' => false,
            'font_family' => 'helvetica',
            'color' => '#000000',
        ];
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
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'texts' => ['required', 'array', 'min:1'],
            'texts.*.content' => ['required', 'string', 'max:255'],
            'texts.*.x' => ['numeric', 'between:0,100'],
            'texts.*.y' => ['numeric', 'between:0,100'],
            'texts.*.font_size' => ['numeric', 'between:0,100'],
            'texts.*.bold' => ['boolean'],
            'texts.*.font_family' => ['nullable', 'in:times,helvetica,arial,courier,greatvibes,dancingscript,caveat'],
            'texts.*.color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'photoBox.x' => ['numeric', 'between:0,100'],
            'photoBox.y' => ['numeric', 'between:0,100'],
            'photoBox.w' => ['numeric', 'between:1,100'],
            'photoBox.h' => ['numeric', 'between:1,100'],
        ]);

        if ($this->image) {
            // Normalisasi: center-crop rasio CR80 + downscale 1075px + rekompres
            $path = app(\App\Services\IdCardImageService::class)->normalizeAndStore($this->image);
        } else {
            $path = $this->existingImagePath;
        }

        abort_unless($path, 422, 'Gambar template wajib diunggah.');

        $data = collect($validated)
            ->except('image', 'photoBox')
            ->merge([
                'image_path' => $path,
                'photo_box' => $this->photoBox,
            ])
            ->all();

        if ($this->editingId) {
            $template = $this->event->idCardTemplates()->findOrFail($this->editingId);
            $oldPath = $template->image_path;
            $template->update($data);

            if ($this->image && $oldPath && $oldPath !== $path) {
                Storage::disk('public')->delete($oldPath);
            }

            session()->flash('id-card-templates-success', 'Template berhasil diperbarui.');
        } else {
            $this->event->idCardTemplates()->create($data);
            session()->flash('id-card-templates-success', 'Template berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function toggleActive(int $id): void
    {
        $template = $this->event->idCardTemplates()->findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);
    }

    public function delete(int $id): void
    {
        $template = $this->event->idCardTemplates()->findOrFail($id);
        Storage::disk('public')->delete($template->image_path);
        $template->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        session()->flash('id-card-templates-success', 'Template dihapus.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->image = null;
        $this->existingImagePath = null;
        $this->texts = IdCardTemplate::DEFAULT_TEXTS;
        $this->photoBox = IdCardTemplate::DEFAULT_PHOTO_BOX;
    }
}
