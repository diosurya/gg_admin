{{-- resources/views/layouts/partials/footer.blade.php --}}
<div class="footer">
    <div class="float-right">
        @php
            $diskUsage = disk_free_space('/') / 1024 / 1024 / 1024; // Convert to GB
            $totalDisk = disk_total_space('/') / 1024 / 1024 / 1024;
            $usedDisk = $totalDisk - $diskUsage;
        @endphp
        {{ number_format($usedDisk, 1) }}GB of <strong>{{ number_format($totalDisk, 1) }}GB</strong> used.
    </div>
    <div>
        <strong>Copyright</strong> Gudang Grosiran &copy; {{ date('Y') }}
        | Version {{ config('app.version', '1.0.0') }} | Support By <a target="_blank" href="https://github.com/diosurya">Dio Putra</a> |
        @if(app()->environment() !== 'production')
            <span class="badge badge-warning">{{ strtoupper(app()->environment()) }}</span>
        @endif
    </div>
</div>