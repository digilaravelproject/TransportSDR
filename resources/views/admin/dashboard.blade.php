@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
<style>
    /* Make dashboard page fullscreen and remove white border */
    body { background: #07080a !important; }
    .content-area { background: transparent !important; padding: 18px 28px !important; }
    /* Reduce heavy rounding so the panel looks wide */
    .dashboard-shell { border-radius: 10px; margin: 0; }
    /* Ensure sidebar still sits flush */
    .sidebar { box-shadow: none; }
</style>

@php
    use App\Models\Trip;
    $totalPieces = Trip::count();
    $delivered = Trip::whereIn('status', ['delivered','completed','paid'])->count();
    $activeTracking = Trip::whereNotIn('status', ['delivered','completed'])->count();

    $year = now()->format('Y');
    $monthly = [];
    for ($m = 1; $m <= 12; $m++) {
        $monthly[] = Trip::whereYear('created_at', $year)->whereMonth('created_at', $m)->count();
    }
    $monthlyJson = json_encode($monthly);
@endphp

<div class="dashboard-shell">
    <div class="top-row">
        <div class="welcome-col">
            <h1 style="margin:0; font-size:28px;">Welcome back, {{ explode(' ', Auth::guard('admin')->user()->name)[0] ?? 'Admin' }}!</h1>
            <p style="margin:4px 0 0;color:#9aa7b7">Your active shipments await. Let's deliver on time.</p>
        </div>

        <div class="cards-col">
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:44px;height:44px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#9be15d">📦</div>
                    <div style="text-align:left">
                        <div class="label">Total trips</div>
                        <div class="value">{{ number_format($totalPieces) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:44px;height:44px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#60a5fa">🚚</div>
                    <div style="text-align:left">
                        <div class="label">Delivered</div>
                        <div class="value">{{ number_format($delivered) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:44px;height:44px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#7c3aed">📍</div>
                    <div style="text-align:left">
                        <div class="label">Active tracking</div>
                        <div class="value">{{ number_format($activeTracking) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-main">
        <div class="left-panel">
            <div class="chart-card">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <h5 style="margin:0">Shipment Volume Trend</h5>
                        <div style="color:#91a6bd;font-size:13px">{{ array_sum($monthly) }} <span style="color:#86efac;font-size:12px">▲ 12% From the last month</span></div>
                    </div>
                    <div>
                        <select class="form-select form-select-sm" id="chart-range" style="background:#0b0d10;color:#c7d6e6;border:none;width:120px">
                            <option>Monthly</option>
                        </select>
                    </div>
                </div>
                <canvas id="volumeChart" style="width:100%;height:220px;margin-top:12px"></canvas>
            </div>

            <div class="activity-card" style="margin-top:16px">
                <h5 style="margin:0 0 8px 0">Shipment Overview</h5>
                <table class="shipment-table">
                    <thead>
                        <tr><th>ID</th><th>Status</th><th>Origin</th><th>Destination</th><th>ETA</th></tr>
                    </thead>
                    <tbody>
                        @php $recent = Trip::latest()->take(4)->get(); @endphp
                        @foreach($recent as $r)
                            <tr>
                                <td>{{ $r->trip_number ?? '#'. $r->id }}</td>
                                <td style="color:#c7d6e6">{{ ucfirst($r->status ?? 'n/a') }}</td>
                                <td>{{ $r->pickup_address ?? '-' }}</td>
                                <td>{{ is_array($r->destination_points) ? implode(', ', $r->destination_points) : ($r->destination_points ?? '-') }}</td>
                                <td>{{ optional($r->trip_date)->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="right-panel">
            <div class="chart-card" style="display:flex;flex-direction:column;gap:12px;align-items:flex-start">
                <div style="width:100%">
                    <h6 style="margin:0">Active Fleet in Transit</h6>
                    <div style="color:#91a6bd;font-size:13px;margin-top:6px">Fuel level <strong style="color:#c7d6e6">85%</strong></div>
                    <div style="color:#91a6bd;font-size:13px">Current load <strong style="color:#c7d6e6">7.5 / 10 t</strong></div>
                </div>
                <div class="truck-blob" style="width:100%">
                    <!-- Simple inline truck illustration -->
                    <svg width="160" height="90" viewBox="0 0 160 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="24" width="110" height="46" rx="8" fill="#111827" stroke="#2a3440" />
                        <rect x="108" y="34" width="44" height="26" rx="6" fill="#0ea5a4" opacity="0.12" />
                        <circle cx="42" cy="74" r="8" fill="#0b1220" stroke="#4b5563" />
                        <circle cx="118" cy="74" r="8" fill="#0b1220" stroke="#4b5563" />
                    </svg>
                </div>
            </div>

            <div class="map-card">
                <h6 style="margin:0 0 8px 0">Active Route</h6>
                <div id="dashboardMap" class="map-placeholder">
                    <!-- Decorative route line -->
                    <svg width="100%" height="100%" viewBox="0 0 400 200" preserveAspectRatio="none" style="position:absolute;left:0;top:0;">
                        <rect width="100%" height="100%" fill="transparent" />
                        <path d="M10 160 C 80 120, 140 40, 200 80 C 260 120, 320 60, 390 40" stroke="#9ae6b4" stroke-width="4" fill="none" stroke-linecap="round" />
                        <circle cx="200" cy="80" r="10" fill="#0b1220" stroke="#9ae6b4" stroke-width="3" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Leaflet for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const monthly = {!! $monthlyJson !!};
    const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const ctx = document.getElementById('volumeChart').getContext('2d');
    const cfg = { type: 'line', data: { labels, datasets: [ { label: 'Trips', data: monthly, borderColor: '#60a5fa', backgroundColor: 'rgba(96,165,250,0.08)', tension:0.4, fill:true } ] }, options: { plugins:{legend:{display:false}}, scales:{x:{grid:{display:false}, ticks:{color:'#97a9bf'}}, y:{grid:{color:'rgba(255,255,255,0.03)'}, ticks:{color:'#97a9bf'}}}, elements:{point:{radius:0}} } };
    new Chart(ctx, cfg);

    // Initialize Leaflet map with a sample route and simulated moving marker
    const map = L.map('dashboardMap', { zoomControl: false, attributionControl: false }).setView([37.8, -96], 4);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    const route = [ [34.0522, -118.2437], [36.1699, -115.1398], [39.7392, -104.9903], [41.8781, -87.6298], [40.7128, -74.0060] ];
    const poly = L.polyline(route, { color: '#9ae6b4', weight:4 }).addTo(map);
    map.fitBounds(poly.getBounds(), { padding: [20,20] });

    const marker = L.circleMarker(route[0], { radius:8, color:'#0b1220', fillColor:'#9ae6b4', fillOpacity:1 }).addTo(map);
    let idx = 0;
    setInterval(() => {
        idx = (idx + 1) % route.length;
        marker.setLatLng(route[idx]);
    }, 2500);
</script>

@endsection