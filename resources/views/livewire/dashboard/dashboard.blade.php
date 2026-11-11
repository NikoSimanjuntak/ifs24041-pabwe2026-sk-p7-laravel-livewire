<div>
    <h1>Dashboard</h1>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
        <div style="background:white;padding:20px;border-radius:12px">
            <small>Total barang</small>
            <h2 id="dashboard-total">{{ $total }}</h2>
        </div>
        <div style="background:white;padding:20px;border-radius:12px">
            <small>Tersedia</small>
            <h2 id="dashboard-available">{{ $available }}</h2>
        </div>
        <div style="background:white;padding:20px;border-radius:12px">
            <small>Stok menipis</small>
            <h2 id="dashboard-low">{{ $low }}</h2>
        </div>
        <div style="background:white;padding:20px;border-radius:12px">
            <small>Habis</small>
            <h2 id="dashboard-out">{{ $out }}</h2>
        </div>
    </div>

    <p><a href="/stocks">Lihat daftar stok</a></p>
</div>
