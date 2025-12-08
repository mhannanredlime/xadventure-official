@props(['route', 'confirm' => 'Are you sure you want to delete this item?'])

<form action="{{ $route }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-icon btn-light text-danger shadow-sm hover-scale"
        onclick="return confirm('{{ $confirm }}');" title="Delete">
        <i class="bi bi-trash"></i>
    </button>
</form>
