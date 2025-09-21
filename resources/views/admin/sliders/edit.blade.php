@extends('layouts.app')

@section('title','Edit Slider')

@section('content')
<h1>Edit Slider</h1>

<form action="{{ route('admin.settings.sliders.update',$slider->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="mb-3">
        <label>Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title',$slider->title) }}">
    </div>

    <div class="mb-3">
        <label>Caption</label>
        <textarea name="caption" class="form-control">{{ old('caption',$slider->caption) }}</textarea>
    </div>

    <div class="mb-3">
        <label>Current Image</label><br>
        <img src="{{ asset('storage/'.$slider->image) }}" width="300" class="mb-2"><br>
        <label>Change Image (2376 x 807)</label>
        <input type="file" name="image" class="form-control">
    </div>

    <div class="mb-3">
        <label>Link (optional)</label>
        <input type="url" name="link" class="form-control" value="{{ old('link',$slider->link) }}">
    </div>

    <div class="mb-3">
        <label>Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order',$slider->sort_order) }}">
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="draft" {{ old('status',$slider->status)=='draft'?'selected':'' }}>Draft</option>
            <option value="published" {{ old('status',$slider->status)=='published'?'selected':'' }}>Published</option>
        </select>
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('admin.settings.sliders.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
