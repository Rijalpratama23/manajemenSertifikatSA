@if (session('success'))
    <p class="admin-alert admin-alert-success" role="status">{{ session('success') }}</p>
@endif

@if (session('error'))
    <p class="admin-alert admin-alert-error" role="alert">{{ session('error') }}</p>
@endif

@if ($errors->any())
    <div class="admin-alert admin-alert-error" role="alert">
        <p>Data belum dapat diproses:</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
