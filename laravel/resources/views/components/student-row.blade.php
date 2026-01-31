@props(['name', 'nim', 'title', 'count', 'status'])

@php
    $statusLower = strtolower($status);
    $badgeClass = $statusLower == 'selesai' || $statusLower == 'finished' ? 'badge-success' : 'badge-warning';
    $badgeStyle = $statusLower == 'selesai' || $statusLower == 'finished'
        ? 'background-color: #d4edda; color: #28a745;'
        : 'background-color: #f7e1c280; color: #d9534f;';
    $dataStatus = $statusLower == 'selesai' || $statusLower == 'finished' ? 'selesai' : 'ongoing';
@endphp

<tr class="inner-row" data-status="{{ $dataStatus }}">
    <td>{{ $name }}</td>
    <td>{{ $nim }}</td>
    <td>{{ $title }}</td>
    <td class="text-center">{{ $count }}</td>
    <td class="text-center">
        <span class="badge {{ $badgeClass }} px-3" style="{{ $badgeStyle }}">
            {{ $status }}
        </span>
    </td>
</tr>