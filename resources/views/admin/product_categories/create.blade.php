@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <h4>Categories</h4>
        <ul id="category-list" class="list-group">
            @foreach($categories as $cat)
                <li class="list-group-item" data-id="{{ $cat->id }}" data-parent="{{ $cat->parent_id }}">
                    {{ $cat->name }}
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    var el = document.getElementById('category-list');
    new Sortable(el, {
        animation: 150,
        onEnd: function (evt) {
            let items = [];
            document.querySelectorAll('#category-list li').forEach((el, index) => {
                items.push({
                    id: el.dataset.id,
                    parent_id: el.dataset.parent || null,
                    order: index
                });
            });

            fetch("{{ route('admin.product-categories.reorder') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ items })
            });
        }
    });
</script>
@endpush
