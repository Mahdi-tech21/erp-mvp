@props(['name' => null])

<textarea {{ $attributes->merge(['id' => $name, 'name' => $name, 'rows' => 3])->class([
    'block w-full rounded-lg border-gray-300 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500',
    'border-red-300 focus:border-red-500 focus:ring-red-500' => $name && $errors->has($name),
]) }}>{{ $slot }}</textarea>
