<div class="row">
    {{-- Main Content --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Brand Information</h5>
            </div>
            <div class="card-body">
                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Brand Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           id="name"
                           name="name"
                           value="{{ old('name', $brand->name ?? '') }}"
                           placeholder="Brand name"
                           required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Slug --}}
                <div class="mb-3">
                    <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('slug') is-invalid @enderror"
                        id="slug"
                        name="slug"
                        value="{{ old('slug') }}"
                        placeholder="brand-slug"
                        required>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $brand->description ?? '') }}</textarea>
                </div>

                {{-- Website --}}
                <div class="mb-3">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" class="form-control" id="website" name="website" value="{{ old('website', $brand->website ?? '') }}">
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $brand->email ?? '') }}">
                </div>

                {{-- Phone --}}
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $brand->phone ?? '') }}">
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" rows="2" class="form-control">{{ old('address', $brand->address ?? '') }}</textarea>
                </div>

                {{-- Country --}}
                <div class="mb-3">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $brand->country ?? '') }}">
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-control @error('status') is-invalid @enderror"
                            id="status"
                            name="status"
                            required>
                        <option value="active" {{ old('status', $brand->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $brand->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Sort Order --}}
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}">
                </div>

                {{-- Featured --}}
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured"
                           class="form-check-input"
                           {{ old('is_featured', $brand->is_featured ?? 0) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_featured">Featured</label>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Logo --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Brand Logo</h5>
            </div>
            <div class="card-body">
                <input type="file"
                       class="form-control @error('logo') is-invalid @enderror"
                       id="logo"
                       name="logo"
                       accept="image/*">
                <small class="form-text text-muted">Max size: 2MB. Stored in <code>/brand/logo</code></small>
                @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if(!empty($brand->logo))
                    <img src="{{ asset('storage/'.$brand->logo) }}" class="img-fluid rounded mt-2" style="max-height:80px">
                @endif
            </div>
        </div>

        {{-- Banner --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Brand Banner</h5>
            </div>
            <div class="card-body">
                <input type="file"
                       class="form-control @error('banner') is-invalid @enderror"
                       id="banner"
                       name="banner"
                       accept="image/*">
                <small class="form-text text-muted">Max size: 5MB. Stored in <code>/brand/banner</code></small>
                @error('banner') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if(!empty($brand->banner))
                    <img src="{{ asset('storage/'.$brand->banner) }}" class="img-fluid rounded mt-2" style="max-height:120px">
                @endif
            </div>
        </div>

        {{-- Submit --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> {{ $submitLabel }}
                    </button>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    nameInput.addEventListener('keyup', function () {
        let slug = this.value.toString()
            .toLowerCase()
            .replace(/\s+/g, '-')      // ganti spasi jadi strip
            .replace(/[^\w\-]+/g, '')  // hapus karakter aneh
            .replace(/\-\-+/g, '-')    // ganti strip berulang jadi satu
            .replace(/^-+/, '')        // hapus strip di depan
            .replace(/-+$/, '');       // hapus strip di belakang
        slugInput.value = slug;
    });

    // Logo preview
    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logoPreview');
    const logoImg = document.getElementById('logoImg');
    logoInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                logoImg.src = ev.target.result;
                logoPreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // Banner preview
    const bannerInput = document.getElementById('banner');
    const bannerPreview = document.getElementById('bannerPreview');
    const bannerImg = document.getElementById('bannerImg');
    bannerInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                bannerImg.src = ev.target.result;
                bannerPreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
});

function removeLogo() {
    document.getElementById('logo').value = '';
    document.getElementById('logoPreview').style.display = 'none';
}

function removeBanner() {
    document.getElementById('banner').value = '';
    document.getElementById('bannerPreview').style.display = 'none';
}
</script>
@endpush

