@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="mb-5 flex items-center justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        <span>{{ session('status') }}</span>
        <button @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
    </div>
@endif

@if (session('warning'))
    <div x-data="{ show: true }" x-show="show"
         class="mb-5 flex items-center justify-between gap-3 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
        <span>{{ session('warning') }}</span>
        <button @click="show = false" class="text-orange-600 hover:text-orange-800">&times;</button>
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
