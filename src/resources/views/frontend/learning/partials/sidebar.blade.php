<aside class="side-panel">
    <a href="{{ route('dashboard') }}" class="brand-mini">
        <span class="mark">Y</span>
        <span>{{ $setting->brand_text }}</span>
    </a>

    <a href="{{ route('learning.profile.edit') }}" class="profile-tile profile-tile-link">
        <div class="avatar">
            @if (auth()->user()->avatar_url)
                <img src="{{ asset('storage/' . auth()->user()->avatar_url) }}" alt="Avatar user">
            @else
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            @endif
        </div>
        <div>
            <h3>{{ auth()->user()->name }}</h3>
            <p>{{ auth()->user()->isPremium() ? 'Premium aktif' : 'Edit Profiles' }}</p>
        </div>
    </a>

    <a href="{{ route('learning.letters') }}" class="menu-item {{ request()->routeIs('learning.letters') ? 'active' : '' }}">
        <span class="menu-icon">Aa</span>
        <span>Huruf</span>
    </a>

    <a href="{{ route('learning.store') }}" class="menu-item {{ request()->routeIs('learning.store', 'learning.premium') ? 'active' : '' }}">
        <span class="menu-icon">T</span>
        <span>Toko Premium</span>
    </a>

    <a href="{{ route('learning.games') }}" class="menu-item {{ request()->routeIs('learning.games', 'learning.tournament', 'learning.duel.*', 'learning.quiz.*') ? 'active' : '' }}">
        <span class="menu-icon">VS</span>
        <span>Turnamen</span>
    </a>

    <a href="{{ route('learning.settings') }}" class="menu-item {{ request()->routeIs('learning.profile.*', 'learning.settings*') ? 'active' : '' }}">
        <span class="menu-icon">S</span>
        <span>Setting</span>
    </a>

    <div class="panel-title">Friend (Active)</div>
    <div class="friend-list">
        @forelse ($friends->take(3) as $friend)
            <div class="friend-card">
                <div class="mini-avatar">{{ strtoupper(substr($friend->name, 0, 1)) }}</div>
                <div>
                    <strong>{{ $friend->name }}</strong><br>
                    <small>Online belajar</small>
                </div>
            </div>
        @empty
            <div class="friend-card offline"><div class="mini-avatar">+</div><div><strong>Belum ada teman</strong><br><small>User baru akan muncul di sini.</small></div></div>
        @endforelse
    </div>

    <div class="panel-title">Friend (Offline)</div>
    <div class="friend-list">
        @foreach ($friends->skip(3)->take(3) as $friend)
            <div class="friend-card offline">
                <div class="mini-avatar">{{ strtoupper(substr($friend->name, 0, 1)) }}</div>
                <div>
                    <strong>{{ $friend->name }}</strong><br>
                    <small>Offline</small>
                </div>
            </div>
        @endforeach
    </div>
</aside>
