<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubCategoryRequest;
use App\Models\EventCategory;
use App\Models\SubCategory;

class SubCategoryController extends Controller
{
    public function store(SubCategoryRequest $request, EventCategory $eventCategory)
    {
        $eventCategory->subCategories()->create($request->validated());

        return redirect()->route('admin.event-categories.show', $eventCategory)->with('success', 'Sub-kategori berhasil ditambahkan.');
    }

    public function edit(SubCategory $subCategory)
    {
        $subCategory->load('eventCategory.event');

        return view('admin.sub-categories.edit', compact('subCategory'));
    }

    public function update(SubCategoryRequest $request, SubCategory $subCategory)
    {
        $validated = $request->validated();

        if (! $subCategory->canEditPrice()) {
            unset($validated['price']);
        }

        $subCategory->update($validated);

        return redirect()->route('admin.event-categories.show', $subCategory->eventCategory)->with('success', 'Sub-kategori berhasil diperbarui.');
    }

    public function destroy(SubCategory $subCategory)
    {
        $eventCategory = $subCategory->eventCategory;

        if (! $subCategory->canDelete()) {
            return back()->withErrors([
                'delete' => 'Sub-kategori tidak dapat dihapus karena sudah ada registrasi aktif.',
            ]);
        }

        $subCategory->delete();

        return redirect()->route('admin.event-categories.show', $eventCategory)->with('success', 'Sub-kategori berhasil dihapus.');
    }
}
