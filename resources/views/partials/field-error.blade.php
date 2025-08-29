{{-- resources/views/partials/field-error.blade.php --}}
@php($name = $field ?? null)

@if ($name && $errors->has($name))
  <div class="error">{{ $errors->first($name) }}</div>
@endif
