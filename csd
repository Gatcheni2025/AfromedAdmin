<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stax · sell companies & API access</title>
    <!-- Font & icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f3f6fc;
            color: #0b1c33;
            line-height: 1.5;
        }

        /* main dashboard structure */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* sidebar – modern glass feel */
        .sidebar {
            width: 280px;
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-right: 1px solid rgba(0,20,50,0.08);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0,0,0,0.02);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2.5rem;
            padding-left: 6px;
        }

        .logo-icon {
            background: #102a4e;
            color: white;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            box-shadow: 0 6px 12px rgba(16,42,78,0.2);
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            color: #102a4e;
        }

        .logo-text span {
            font-weight: 400;
            color: #5b6f91;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.75rem 1rem;
            margin: 4px 0;
            border-radius: 14px;
            color: #1e2f4a;
            font-weight: 500;
            transition: all 0.15s;
            cursor: default;
        }

        .nav-item i {
            width: 24px;
            font-size: 1.25rem;
            color: #3b4f6e;
        }

        .nav-item.active {
            background: #ffffff;
            box-shadow: 0 6px 14px rgba(0,20,50,0.06);
            color: #0b1c33;
            font-weight: 600;
        }

        .nav-item.active i {
            color: #1b3a6b;
        }

        .nav-item:not(.active):hover {
            background: rgba(255,255,255,0.5);
        }

        .user-chip {
            margin-top: auto;
            background: white;
            border-radius: 30px;
            padding: 0.7rem 1rem 0.7rem 0.7rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 6px 12px rgba(0,0,0,0.02);
            cursor: default;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 30px;
            background: linear-gradient(145deg, #1a3f6e, #102a4e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-email {
            font-size: 0.75rem;
            color: #4e6382;
        }

        /* main content area */
        .main {
            flex: 1;
            padding: 2rem 2.5rem;
            overflow-y: auto;
        }

        /* header */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            font-weight: 700;
            font-size: 1.9rem;
            letter-spacing: -0.02em;
            color: #102a4e;
        }

        .page-title p {
            color: #4f658a;
            font-weight: 500;
            font-size: 0.95rem;
            margin-top: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
        }

        .btn-soft {
            background: white;
            border: none;
            padding: 0.6rem 1.4rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #1d3a5c;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: default;
            border: 1px solid rgba(0,20,40,0.06);
        }

        .btn-primary {
            background: #102a4e;
            border: none;
            padding: 0.6rem 1.8rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            color: white;
            box-shadow: 0 8px 18px rgba(16,42,78,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: default;
        }

        /* balance cards */
        .row-cards {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            border-radius: 28px;
            padding: 1.5rem 2rem 1.5rem 1.8rem;
            box-shadow: 0 20px 30px -10px rgba(16,42,78,0.1);
            flex: 1 1 240px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid rgba(255,255,255,0.5);
            backdrop-filter: blur(4px);
        }

        .stat-icon {
            background: #eef3fc;
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a3f6e;
            font-size: 28px;
        }

        .stat-content h3 {
            font-size: 0.95rem;
            font-weight: 500;
            color: #4e6382;
            letter-spacing: 0.02em;
        }

        .stat-content .value {
            font-weight: 700;
            font-size: 2.1rem;
            line-height: 1.2;
            color: #0b1c33;
        }

        .badge-new {
            background: #d2e0ff;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.7rem;
            color: #1b3a6b;
            margin-left: 8px;
        }

        /* section header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0 1.2rem 0;
        }

        .section-header h2 {
            font-weight: 600;
            font-size: 1.4rem;
            color: #102a4e;
        }

        .pill-filter {
            background: white;
            border-radius: 40px;
            padding: 0.3rem;
            display: flex;
            gap: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .pill {
            padding: 0.4rem 1.2rem;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #4e6382;
            cursor: default;
        }

        .pill.active {
            background: #102a4e;
            color: white;
        }

        /* company cards grid */
        .company-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .company-card {
            background: white;
            border-radius: 24px;
            padding: 1.5rem 1.3rem 1.3rem 1.3rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.02);
            transition: transform 0.1s;
            border: 1px solid rgba(0,0,0,0.02);
            display: flex;
            flex-direction: column;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-badge {
            background: #ecf2fc;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.9rem;
            color: #1b3a6b;
            text-transform: uppercase;
        }

        .price-tag {
            font-weight: 700;
            font-size: 1.5rem;
            color: #102a4e;
        }

        .company-desc {
            font-weight: 600;
            font-size: 1.2rem;
            margin: 0.8rem 0 0.4rem;
        }

        .company-meta {
            display: flex;
            gap: 12px;
            font-size: 0.8rem;
            color: #52688b;
            margin: 0.5rem 0 1rem;
        }

        .company-meta i {
            margin-right: 4px;
        }

        .api-options {
            margin-top: 0.4rem;
            border-top: 1px dashed #d9e2f0;
            padding-top: 1rem;
        }

        .api-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.9rem;
            padding: 0.3rem 0;
        }

        .api-price {
            font-weight: 700;
            color: #102a4e;
        }

        .btn-outline-sm {
            border: 1px solid #c9d6e8;
            background: transparent;
            border-radius: 30px;
            padding: 0.25rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1d3a5c;
            cursor: default;
        }

        .btn-outline-sm i {
            font-size: 0.7rem;
            margin-left: 4px;
        }

        .divider {
            border-top: 1px solid #eef2f8;
            margin: 1rem 0;
        }

        .company-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.3rem;
        }

        .status {
            font-size: 0.8rem;
            font-weight: 500;
            color: #2c7a4d;
            background: #e2f3e4;
            padding: 0.2rem 0.8rem;
            border-radius: 40px;
        }

        /* api pricing summary panel */
        .api-panel {
            background: white;
            border-radius: 28px;
            padding: 1.5rem 2rem;
            margin: 2rem 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 12px 28px -8px rgba(16,42,78,0.08);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .api-panel h3 {
            font-weight: 600;
            font-size: 1.2rem;
        }

        .api-pricing-cards {
            display: flex;
            gap: 24px;
        }

        .api-tier {
            background: #f5f9ff;
            border-radius: 18px;
            padding: 0.6rem 1.4rem;
            font-weight: 500;
            border: 1px solid #d6e3f5;
        }

        .api-tier strong {
            font-size: 1.2rem;
            margin-right: 6px;
            color: #102a4e;
        }

        .api-tier.v {
            border-left: 5px solid #1f3f6e;
        }
        .api-tier.m {
            border-left: 5px solid #446d9c;
        }
        .api-tier.ax {
            border-left: 5px solid #102a4e;
        }

        .footnote {
            font-size: 0.8rem;
            color: #5d718e;
            margin-top: 0.8rem;
        }

        .inline-icon {
            margin-right: 6px;
            color: #2f4d75;
        }

        hr {
            border: none;
            border-top: 1px solid #dfe7f2;
            margin: 1.5rem 0;
        }

        /* Reference row (dashboard hint) */
        .ref-note {
            background: #e9effa;
            border-radius: 60px;
            padding: 0.6rem 1.4rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1b3a6b;
            margin-bottom: 24px;
            border: 1px solid #ccd9f0;
        }

        .ref-note i {
            font-size: 1rem;
        }

        /* no unwated hover effects – keep clean */
        button, .btn-soft, .btn-primary, .pill, .btn-outline-sm {
            pointer-events: none;   /* demo: interactive looks but no actual click */
        }

        /* responsiveness */
        @media (max-width: 900px) {
            .dashboard { flex-direction: column; }
            .sidebar { width: 100%; backdrop-filter: none; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- SIDEBAR · modern & logged-in reference -->
        <div class="sidebar">
            <div class="logo-area">
                <div class="logo-icon">SX</div>
                <div class="logo-text">Stax<span>.co</span></div>
            </div>
            <div class="nav-item active">
                <i class="fas fa-th-large"></i> 
                <span>Dashboard</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-building"></i> 
                <span>Companies</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-code"></i> 
                <span>API keys</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-credit-card"></i> 
                <span>Billing</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-users"></i> 
                <span>Team</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-sliders-h"></i> 
                <span>Settings</span>
            </div>

            <!-- Logged-in user chip (realistic) -->
            <div class="user-chip">
                <div class="avatar">JD</div>
                <div class="user-info">
                    <div class="user-name">Jessie Du</div>
                    <div class="user-email">j.du@stax.co</div>
                </div>
                <i class="fas fa-chevron-down" style="color:#9aafca; font-size:12px;"></i>
            </div>
        </div>

        <!-- MAIN PANEL (dashboard area) -->
        <div class="main">

            <!-- Reference badge as requested: "logged in using reference" hint -->
            <div class="ref-note">
                <i class="fas fa-link"></i> 
                <span>Logged in using reference · 2FA enabled</span>
                <i class="fas fa-check-circle" style="color:#1f6e43;"></i>
            </div>

            <div class="main-header">
                <div class="page-title">
                    <h1>Company marketplace</h1>
                    <p>Shelf LLCs · API integration ready</p>
                </div>
                <div class="action-buttons">
                    <div class="btn-soft"><i class="fas fa-download"></i> Export</div>
                    <div class="btn-primary"><i class="fas fa-plus"></i> New order</div>
                </div>
            </div>

            <!-- quick balance / portfolio stats -->
            <div class="row-cards">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-content">
                        <h3>Portfolio value</h3>
                        <div class="value">$ 1,284,500</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-content">
                        <h3>Active companies</h3>
                        <div class="value">18 <span class="badge-new">+3</span></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-code-branch"></i></div>
                    <div class="stat-content">
                        <h3>API calls (30d)</h3>
                        <div class="value">22.4k</div>
                    </div>
                </div>
            </div>

            <!-- API PRICING HIGHLIGHT (exactly as requested) -->
            <div class="api-panel">
                <div>
                    <h3><i class="fas fa-bolt inline-icon"></i> API access — Sustainable Development and Environmental Consulting (SDEC - TUEH4738ZA)</h3>
                    <p class="footnote" style="margin-top:4px;">Per‑company licensing, starting at</p>
                </div>
                <div class="api-pricing-cards">
                    <div class="api-tier v"><strong>$25</strong> VISA only</div>
                    <div class="api-tier m"><strong>$55</strong> VISA + Master</div>
                    <div class="api-tier ax"><strong>$100</strong> Amex + all</div>
                </div>
            </div>

            <!-- filter area -->
            <div class="section-header">
                <h2>Available LLCs</h2>
                <div class="pill-filter">
                    <span class="pill active">All</span>
                    <span class="pill">Delaware</span>
                    <span class="pill">Wyoming</span>
                    <span class="pill">API ready</span>
                </div>
            </div>

            <!-- COMPANY GRID (selling LLCs) -->
            <div class="company-grid">
                <!-- card 1 -->
                <div class="company-card">
                    <div class="card-header">
                        <span class="company-badge">WY LLC</span>
                        <span class="price-tag">$799</span>
                    </div>
                    <div class="company-desc">Yellowstone Consulting</div>
                    <div class="company-meta"><i class="fas fa-calendar-alt"></i> 2024 · <i class="fas fa-map-pin"></i> Casper</div>
                    <!-- api options rows -->
                    <div class="api-options">
                        <div class="api-row"><span><i class="far fa-circle"></i> VISA api</span> <span class="api-price">$25</span></div>
                        <div class="api-row"><span><i class="fas fa-check-circle" style="color:#1d3a5c;"></i> VISA+Master</span> <span class="api-price">$55</span></div>
                        <div class="api-row"><span><i class="far fa-star"></i> +Amex</span> <span class="api-price">$100</span></div>
                    </div>
                    <div class="company-footer">
                        <span class="status"><i class="fas fa-check"></i> active</span>
                        <span class="btn-outline-sm">add API <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- card 2 -->
                <div class="company-card">
                    <div class="card-header">
                        <span class="company-badge">DE LLC</span>
                        <span class="price-tag">$1,250</span>
                    </div>
                    <div class="company-desc">Brandywine Holdings</div>
                    <div class="company-meta"><i class="fas fa-calendar-alt"></i> 2023 · <i class="fas fa-map-pin"></i> Wilmington</div>
                    <div class="api-options">
                        <div class="api-row"><span><i class="far fa-circle"></i> VISA</span> <span class="api-price">$25</span></div>
                        <div class="api-row"><span><i class="fas fa-check-circle" style="color:#1d3a5c;"></i> VISA+Master</span> <span class="api-price">$55</span></div>
                        <div class="api-row"><span><i class="far fa-star"></i> +Amex</span> <span class="api-price">$100</span></div>
                    </div>
                    <div class="company-footer">
                        <span class="status"><i class="fas fa-check"></i> active</span>
                        <span class="btn-outline-sm">add API <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- card 3: with Master/Visa emphasis -->
                <div class="company-card">
                    <div class="card-header">
                        <span class="company-badge">NV LLC</span>
                        <span class="price-tag">$1,090</span>
                    </div>
                    <div class="company-desc">Silver Summit LLC</div>
                    <div class="company-meta"><i class="fas fa-calendar-alt"></i> 2024 · <i class="fas fa-map-pin"></i> Reno</div>
                    <div class="api-options">
                        <div class="api-row"><span><i class="far fa-circle"></i> VISA</span> <span class="api-price">$25</span></div>
                        <div class="api-row"><span><i class="fas fa-check-circle" style="color:#1d3a5c;"></i> VISA+Master</span> <span class="api-price">$55</span></div>
                        <div class="api-row"><span><i class="far fa-star"></i> +Amex</span> <span class="api-price">$100</span></div>
                    </div>
                    <div class="company-footer">
                        <span class="status"><i class="fas fa-check"></i> active</span>
                        <span class="btn-outline-sm">add API <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>

                <!-- card 4 - older LLC but with all API prices -->
                <div class="company-card">
                    <div class="card-header">
                        <span class="company-badge">FL LLC</span>
                        <span class="price-tag">$595</span>
                    </div>
                    <div class="company-desc">Sunshine Consulting</div>
                    <div class="company-meta"><i class="fas fa-calendar-alt"></i> 2022 · <i class="fas fa-map-pin"></i> Miami</div>
                    <div class="api-options">
                        <div class="api-row"><span><i class="far fa-circle"></i> VISA</span> <span class="api-price">$25</span></div>
                        <div class="api-row"><span><i class="fas fa-check-circle" style="color:#1d3a5c;"></i> VISA+Master</span> <span class="api-price">$55</span></div>
                        <div class="api-row"><span><i class="far fa-star"></i> +Amex</span> <span class="api-price">$100</span></div>
                    </div>
                    <div class="company-footer">
                        <span class="status"><i class="fas fa-clock"></i> pending</span>
                        <span class="btn-outline-sm">add API <i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>
            </div>

            <!-- additional reference: explicit API addon block, highlighting pricing again -->
            <hr>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                <div>
                    <span style="font-weight: 600; background:#f0f5ff; padding:0.5rem 1.2rem; border-radius:30px;">
                        <i class="fas fa-credit-card" style="margin-right:8px;"></i>API add‑on for any LLC:
                    </span>
                </div>
                <div style="display: flex; gap: 30px; font-weight: 500;">
                    <span><i class="fab fa-cc-visa" style="color:#1a3f6e;"></i> VISA <strong>$25</strong></span>
                    <span><i class="fab fa-cc-mastercard" style="color:#f79e1b;"></i> +Master <strong>$55</strong></span>
                    <span><i class="fab fa-cc-amex" style="color:#0070ba;"></i> American Express <strong>$100</strong></span>
                </div>
            </div>

            <!-- extra line of recent activity (makes dashboard realistic) -->
            <div style="margin-top: 40px; background: #ffffff; border-radius: 24px; padding: 1.5rem;">
                <div style="display: flex; gap: 20px; align-items: center;">
                    <i class="fas fa-exchange-alt" style="background:#eef3fc; padding:0.8rem; border-radius:50%;"></i>
                    <div><strong>Last API sync</strong> · 12 minutes ago  (VISA: 182 calls, Master: 93, Amex: 27)</div>
                    <span style="margin-left: auto; color:#4f658a;"><i class="far fa-clock"></i> live</span>
                </div>
            </div>

            <!-- subtle footer note that includes 'reference' text to satisfy -->
            <div style="margin-top: 2rem; font-size: 0.8rem; color:#8298bb; border-top: 1px solid #e2ecfe; padding-top: 1.5rem; display: flex; gap: 40px;">
                <span><i class="fas fa-check-circle" style="color:#1f6e43;"></i> logged in using reference (session: AX91·JD)</span>
                <span><i class="fas fa-shield-alt"></i> API rate limits: 1000/min</span>
                <span>© Stax · sell companies & API</span>
            </div>
        </div>
    </div>
</body>
</html>