<aside class="side-panel">
    <a href="{{ route('dashboard') }}" class="brand-mini">
        <span class="mark">Y</span>
        <span>{{ $setting->brand_text }}</span>
    </a>

    <div class="profile-tile">
        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div>
            <h3>{{ auth()->user()->name }}</h3>
            <p>“Hari yang cerah”</p>
        </div>
    </div>

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
