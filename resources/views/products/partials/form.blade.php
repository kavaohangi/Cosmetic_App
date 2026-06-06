@php $product = $product ?? null; @endphp
<div class="card grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="form-label" for="name">Nom du produit</label>
        <input id="name" type="text" name="name" value="{{ old('name', $product?->name) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="sku">SKU</label>
        <input id="sku" type="text" name="sku" value="{{ old('sku', $product?->sku) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="category">Catégorie</label>
        <input id="category" type="text" name="category" value="{{ old('category', $product?->category) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="price">Prix (DH)</label>
        <input id="price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product?->price ?? 0) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="stock">Stock</label>
        <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', $product?->stock ?? 0) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="seuil_alerte">Seuil d'alerte</label>
        <input id="seuil_alerte" type="number" min="0" name="seuil_alerte" value="{{ old('seuil_alerte', $product?->seuil_alerte ?? 10) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="image_url">Image (URL)</label>
        <input id="image_url" type="text" name="image_url" value="{{ old('image_url', $product?->image_url) }}" class="form-input">
    </div>
    <div class="sm:col-span-2">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="form-input">{{ old('description', $product?->description) }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true)) class="rounded border-gray-300 text-[#6366F1] focus:ring-[#6366F1]">
            Produit actif
        </label>
    </div>
</div>
