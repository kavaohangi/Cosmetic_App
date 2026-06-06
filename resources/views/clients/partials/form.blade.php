@php $client = $client ?? null; @endphp
<div class="card grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="form-label" for="name">Nom / Raison sociale</label>
        <input id="name" type="text" name="name" value="{{ old('name', $client?->name) }}" class="form-input" required>
    </div>
    <div>
        <label class="form-label" for="type">Type</label>
        <select id="type" name="type" class="form-input">
            @foreach (['pharmacie', 'boutique', 'grossiste', 'particulier'] as $type)
                <option value="{{ $type }}" @selected(old('type', $client?->type) === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="ville">Ville</label>
        <input id="ville" type="text" name="ville" value="{{ old('ville', $client?->ville) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $client?->email) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="phone">Téléphone</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $client?->phone) }}" class="form-input">
    </div>
    <div class="sm:col-span-2">
        <label class="form-label" for="address">Adresse</label>
        <input id="address" type="text" name="address" value="{{ old('address', $client?->address) }}" class="form-input">
    </div>
</div>
