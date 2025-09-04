<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')

    <x-input label="Name" name="name" id="name" :value="old('name', optional($developer)->name)" :error="$errors->first('name')" />
    <x-input label="Email" name="email" id="email" type="email" :value="old('email', optional($developer)->email)" :error="$errors->first('email')" />
    <x-input label="Phone" name="phone" id="phone" :value="old('phone', optional($developer)->phone)" :error="$errors->first('phone')" />
    <x-input label="Password" name="password" id="password" type="password" :error="$errors->first('password')" />

    <div class="flex gap-2">
        <x-button href="/developer" variant="secondary">Back</x-button>
        <x-button type="submit">Save</x-button>
    </div>
</form>
