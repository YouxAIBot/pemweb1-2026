<aside class="right-panel">
    <div class="stat-card">
        <h3>{{ $profile->language?->flag_label ?: 'FLAG' }} {{ $profile->language?->name ?? 'Bahasa' }}</h3>
        <div class="stat-row"><span>Level Awal</span><strong>{{ $profile->start_level_number }}</strong></div>
        <div class="stat-row"><span>Streak</span><strong>{{ $profile->streak }} hari</strong></div>
        <div class="stat-row"><span>XP</span><strong>{{ $profile->total_xp }}</strong></div>
    </div>

    <div class="stat-card">
        <h3>Misi Harian</h3>
        @foreach ($missions as $mission)
            @php
                $target = max((int) $mission->target, 1);
                $progress = min((int) ($mission->progress_value ?? 0), $target);
                $percent = round(($progress / $target) * 100);
            @endphp
            <div class="mission-card {{ ($mission->is_completed ?? false) ? 'completed' : '' }}">
                <strong>{{ $mission->title }}</strong>
                <div class="mission-progress"><span style="width:{{ $percent }}%"></span></div>
                <div class="stat-row" style="margin-top:.35rem"><span>{{ $progress }}/{{ $target }}</span><span>{{ $mission->unit_label }}</span></div>
            </div>
        @endforeach
    </div>

    <div class="stat-card">
        <h3>Menu</h3>
        <nav class="menu-list">
            @foreach ($menus as $menu)
                <a href="{{ $menu->url ?: '#' }}" class="menu-item {{ $loop->first ? 'active' : '' }}">
                    <span class="menu-icon">{{ $menu->icon_label ?: '•' }}</span>
                    <span>{{ $menu->label }}</span>
                </a>
            @endforeach
        </nav>
    </div>
</aside>
