<div class="siami-welcome-banner">
    <div class="siami-welcome-content">
        <div class="siami-welcome-left">
            <div class="siami-welcome-badge">{{ $cycleLabel }}</div>
            <h2 class="siami-welcome-title">Selamat datang, {{ $name }} 👋</h2>
            <p class="siami-welcome-subtitle">{{ $subtitle }}</p>
            <div class="siami-welcome-meta">
                <span>📅 {{ $date }}</span>
                @if($cycleName)
                    <span class="siami-divider">•</span>
                    <span>🔄 Siklus Aktif: <strong>{{ $cycleName }}</strong></span>
                @endif
            </div>
        </div>
        <div class="siami-welcome-illustration">
            <svg viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="30" width="160" height="110" rx="12" fill="white" fill-opacity="0.15"/>
                <rect x="35" y="50" width="60" height="8" rx="4" fill="white" fill-opacity="0.6"/>
                <rect x="35" y="65" width="90" height="6" rx="3" fill="white" fill-opacity="0.4"/>
                <rect x="35" y="78" width="75" height="6" rx="3" fill="white" fill-opacity="0.4"/>
                <rect x="35" y="100" width="40" height="28" rx="6" fill="white" fill-opacity="0.25"/>
                <rect x="82" y="100" width="40" height="28" rx="6" fill="white" fill-opacity="0.25"/>
                <rect x="129" y="100" width="40" height="28" rx="6" fill="white" fill-opacity="0.25"/>
                <circle cx="55" cy="114" r="8" fill="white" fill-opacity="0.5"/>
                <circle cx="102" cy="114" r="8" fill="white" fill-opacity="0.5"/>
                <circle cx="149" cy="114" r="8" fill="white" fill-opacity="0.5"/>
            </svg>
        </div>
    </div>
</div>
