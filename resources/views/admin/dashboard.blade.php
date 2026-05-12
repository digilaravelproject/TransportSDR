@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

@php
    // All data prepared in controller: expect variables available.
    // Fallback for monthlyJson if not provided.
    $monthlyJson = $monthlyJson ?? json_encode($monthly ?? []);
@endphp

<div class="dashboard-shell">
    <div class="top-row">
        <div class="welcome-col">
            <h1 style="margin:0; font-size:28px;">Welcome back, {{ explode(' ', Auth::guard('admin')->user()->name)[0] ?? 'Admin' }}!</h1>
            <p style="margin:4px 0 0;color:#9aa7b7" class="mb-3">Your active shipments await. Let's deliver on time.</p>
        </div>

        <div class="cards-col">
            <!-- Trips -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#9be15d">📦</div>
                    <div style="text-align:left">
                        <div class="label">Trips</div>
                        <div class="value">{{ number_format($totalPieces) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#60a5fa">🚚</div>
                    <div style="text-align:left">
                        <div class="label">Delivered</div>
                        <div class="value">{{ number_format($delivered) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#7c3aed">📍</div>
                    <div style="text-align:left">
                        <div class="label">Active</div>
                        <div class="value">{{ number_format($activeTracking) }}</div>
                    </div>
                </div>
            </div>

            <!-- Vehicles -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#f97316">🚗</div>
                    <div style="text-align:left">
                        <div class="label">Vehicles</div>
                        <div class="value">{{ number_format($totalVehicles) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#10b981">✓</div>
                    <div style="text-align:left">
                        <div class="label">Active</div>
                        <div class="value">{{ number_format($activeVehicles) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#fbbf24">⚙️</div>
                    <div style="text-align:left">
                        <div class="label">Maint.</div>
                        <div class="value">{{ number_format($vehicleInMaintenance) }}</div>
                    </div>
                </div>
            </div>

            <!-- Staff -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#6366f1">👥</div>
                    <div style="text-align:left">
                        <div class="label">Staff</div>
                        <div class="value">{{ number_format($totalStaff) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#14b8a6">✔</div>
                    <div style="text-align:left">
                        <div class="label">Working</div>
                        <div class="value">{{ number_format($activeStaff) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#f472b6">📋</div>
                    <div style="text-align:left">
                        <div class="label">Leave</div>
                        <div class="value">{{ number_format($staffOnLeave) }}</div>
                    </div>
                </div>
            </div>

            <!-- Leads -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#8b5cf6">📞</div>
                    <div style="text-align:left">
                        <div class="label">Leads</div>
                        <div class="value">{{ number_format($totalLeads) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#06b6d4">⭐</div>
                    <div style="text-align:left">
                        <div class="label">Qualified</div>
                        <div class="value">{{ number_format($qualifiedLeads) }}</div>
                    </div>
                </div>
            </div>

            <!-- Customers -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#ec4899">👤</div>
                    <div style="text-align:left">
                        <div class="label">Customers</div>
                        <div class="value">{{ number_format($totalCustomers) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#a78bfa">🔄</div>
                    <div style="text-align:left">
                        <div class="label">Active</div>
                        <div class="value">{{ number_format($activeCustomers) }}</div>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#34d399">💳</div>
                    <div style="text-align:left">
                        <div class="label">Payments</div>
                        <div class="value">{{ number_format($totalPayments) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#4ade80">✓</div>
                    <div style="text-align:left">
                        <div class="label">Complete</div>
                        <div class="value">{{ number_format($completedPayments) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#fca5a5">⏱</div>
                    <div style="text-align:left">
                        <div class="label">Pending</div>
                        <div class="value">{{ number_format($pendingPayments) }}</div>
                    </div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#e0e7ff">📦</div>
                    <div style="text-align:left">
                        <div class="label">Inventory</div>
                        <div class="value">{{ number_format($totalInventoryItems) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#fbbf24">⚠️</div>
                    <div style="text-align:left">
                        <div class="label">Low Stock</div>
                        <div class="value">{{ number_format($lowStockItems) }}</div>
                    </div>
                </div>
            </div>

            <!-- Shifts -->
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#fbbf24">⏰</div>
                    <div style="text-align:left">
                        <div class="label">Shifts</div>
                        <div class="value">{{ number_format($totalShifts) }}</div>
                    </div>
                </div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-pill">
                    <div style="width:36px;height:36px;border-radius:8px;background:#111827;display:flex;align-items:center;justify-content:center;color:#60a5fa">▶</div>
                    <div style="text-align:left">
                        <div class="label">Active</div>
                        <div class="value">{{ number_format($activeShifts) }}</div>
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
                        <h5 style="margin:0">Shipment Trend</h5>
                        <div style="color:#91a6bd;font-size:11px">{{ array_sum($monthly) }} <span style="color:#86efac;font-size:10px">▲ 12%</span></div>
                    </div>
                </div>
                <canvas id="volumeChart" style="width:100%;height:100px;margin-top:6px"></canvas>
            </div>

            <div class="activity-card">
                <h5 style="margin:0 0 6px 0">Recent Shipments</h5>
                <table class="shipment-table">
                    <thead>
                        <tr><th>ID</th><th>Status</th><th>Origin</th><th>Dest.</th><th>ETA</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $r)
                            <tr>
                                <td>{{ $r->trip_number ?? '#'. $r->id }}</td>
                                <td style="color:#c7d6e6">{{ ucfirst($r->status ?? 'n/a') }}</td>
                                <td>{{ substr($r->pickup_address ?? '-', 0, 15) }}{{ strlen($r->pickup_address ?? '') > 15 ? '...' : '' }}</td>
                                <td>
                                    @if(!empty($r->destination_points))
                                        {{ substr(collect($r->destination_points)->pluck('name')->implode(', '), 0, 12) }}...
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="font-size:10px">{{ optional($r->trip_date)->format('m-d') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="right-panel">
            <div class="chart-card">
                <h6 style="margin:0">Fleet Status</h6>
                <div style="color:#91a6bd;font-size:11px;margin-top:4px">
                    <div>Vehicles: <strong style="color:#c7d6e6">{{ $activeVehicles }}/{{ $totalVehicles }}</strong></div>
                    <div>Staff: <strong style="color:#c7d6e6">{{ $activeStaff }}/{{ $totalStaff }}</strong></div>
                    <div>Trips: <strong style="color:#c7d6e6">{{ $activeTracking }} active</strong></div>
                </div>
                <svg width="100%" height="70" viewBox="0 0 160 70" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-top:4px;">
                    <rect x="4" y="14" width="70" height="40" rx="6" fill="#111827" stroke="#2a3440" />
                    <rect x="80" y="18" width="70" height="26" rx="4" fill="#0ea5a4" opacity="0.12" stroke="#0ea5a4" />
                    <circle cx="32" cy="58" r="6" fill="#0b1220" stroke="#4b5563" />
                    <circle cx="78" cy="58" r="6" fill="#0b1220" stroke="#4b5563" />
                </svg>
            </div>

            <div class="chart-card">
                <h6 style="margin:0">Quick Stats</h6>
                <div style="color:#91a6bd;font-size:10px;margin-top:4px;line-height:1.6">
                    <div>💰 Payments: <strong style="color:#34d399">{{ $completedPayments }}</strong></div>
                    <div>📞 Leads: <strong style="color:#06b6d4">{{ $qualifiedLeads }}</strong></div>
                    <div>⏰ Shifts: <strong style="color:#fbbf24">{{ $activeShifts }}</strong></div>
                    <div>📦 Inventory: <strong style="color:#e0e7ff">{{ $lowStockItems }}</strong> low</div>
                </div>
            </div>

            <div class="map-card">
                <h6 style="margin:0 0 4px 0">Route Map</h6>
                <div id="dashboardMap" class="map-placeholder">
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