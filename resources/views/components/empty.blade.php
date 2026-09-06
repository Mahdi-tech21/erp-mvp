@props(['cols' => 1])

<tr>
    <td colspan="{{ $cols }}" class="px-4 py-12 text-center text-sm text-gray-400">
        {{ $slot }}
    </td>
</tr>
