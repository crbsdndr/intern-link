<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @include('components.form-errors')
    <div class="mb-4">
        <label class="block mb-1">Name</label>
        <input type="text" name="name" class="w-full p-2 border rounded" value="{{ old('name', optional($student)->name) }}">
    </div>
    <div class="mb-4">
        <label class="block mb-1">Email</label>
        <input type="email" name="email" class="w-full p-2 border rounded" value="{{ old('email', optional($student)->email) }}">
    </div>
    <div class="mb-4">
        <label class="block mb-1">Phone</label>
        <input type="text" name="phone" class="w-full p-2 border rounded" value="{{ old('phone', optional($student)->phone) }}">
    </div>
    <div class="mb-4">
        <label class="block mb-1">Password</label>
        <input type="password" name="password" class="w-full p-2 border rounded">
    </div>
    <div class="mb-4">
        <label class="block mb-1">Student Number</label>
        <input type="text" name="student_number" class="w-full p-2 border rounded" value="{{ old('student_number', optional($student)->student_number) }}">
    </div>
    <div class="mb-4">
        <label class="block mb-1">National Student Number</label>
        <input type="text" name="national_sn" class="w-full p-2 border rounded" value="{{ old('national_sn', optional($student)->national_sn) }}">
    </div>
    <div class="mb-4">
        <label class="block mb-1">Major</label>
        <input type="text" name="major" class="w-full p-2 border rounded" value="{{ old('major', optional($student)->major) }}">
    </div>
    <div class="mb-4">
        <label class="block mb-1">Batch</label>
        <input type="text" name="batch" class="w-full p-2 border rounded" value="{{ old('batch', optional($student)->batch) }}">
    </div>
    <div class="mb-4">
        <label class="block mb-1">Notes</label>
        <textarea name="notes" class="w-full p-2 border rounded">{{ old('notes', optional($student)->notes) }}</textarea>
    </div>
    <div class="mb-4">
        <label class="block mb-1">Photo</label>
        <input type="text" name="photo" class="w-full p-2 border rounded" value="{{ old('photo', optional($student)->photo) }}">
    </div>
    <a href="/student" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">Back</a>
    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Save</button>
</form>
