<!DOCTYPE html>
<html>

<head>
    <title>NR Sports - Live Football Scores & Fixtures</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('images/logo.jpg') }}" alt="NR Sports" class="logo me-2" style="width:40px;">
                NR Sports
            </a>
        </div>
    </nav>

    <!-- Tabs -->
    <div class="container py-4">
        <ul class="nav nav-tabs mb-4" id="footballTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="live-tab" data-bs-toggle="tab" data-bs-target="#live"
                    type="button">
                    Live Matches
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="fixtures-tab" data-bs-toggle="tab" data-bs-target="#fixtures"
                    type="button">
                    Upcoming Fixtures
                </button>
            </li>
        </ul>

        <div class="tab-content" id="footballTabsContent">
            <!-- Live Matches -->
            <div class="tab-pane fade show active" id="live">
                <div id="matches" class="row justify-content-center"></div>
            </div>

            <!-- Fixtures -->
            <div class="tab-pane fade" id="fixtures">
                <div id="fixtures-list" class="row justify-content-center"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let matchesData = [];
        let fixturesData = [];

        // Fetch live matches
        function fetchLiveMatches() {
            $.get('{{ url('/football/live-api') }}', function(data) {
                matchesData = data.response || data; // Adjust for your API structure
                renderMatches();
            });
        }

        function renderMatches() {
            let html = '';
            if (!matchesData.length) {
                html = '<div class="alert alert-info text-center">No live matches right now.</div>';
            } else {
                matchesData.forEach((match, index) => {
                    let elapsed = match.fixture.status.elapsed ?? 0;
                    let status = match.fixture.status.short;

                    // Goals with scorer names
                    let homeGoalsHtml = '';
                    let awayGoalsHtml = '';

                    if (match.events && match.events.length) {
                        match.events.forEach(ev => {
                            if (ev.type === "Goal") {
                                let timeText = ev.time.elapsed;
                                if (ev.time.extra) timeText += `+${ev.time.extra}`;
                                if (ev.team.id === match.teams.home.id) {
                                    homeGoalsHtml +=
                                        `<div class="goal-item">${timeText}' ${ev.player.name}</div>`;
                                } else if (ev.team.id === match.teams.away.id) {
                                    awayGoalsHtml +=
                                        `<div class="goal-item">${ev.player.name} ${timeText}'</div>`;
                                }
                            }
                        });
                    }

                    html += `
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="match-card text-center p-3 shadow-sm rounded">
                        <div class="league mb-2 d-flex align-items-center justify-content-center">
                            <img src="${match.league.logo}" alt="${match.league.name}" class="me-2" width="25">
                            <span class="fw-semibold">${match.league.name}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center my-3">
                            <div class="text-center flex-fill">
                                <img src="${match.teams.home.logo}" class="team-logo mb-2" width="45"><br>
                                <div class="team-name">${match.teams.home.name}</div>
                                <div class="goal-scorers">${homeGoalsHtml}</div>
                            </div>
                            <div class="score fs-4 fw-bold">${match.goals.home ?? 0} - ${match.goals.away ?? 0}</div>
                            <div class="text-center flex-fill">
                                <img src="${match.teams.away.logo}" class="team-logo mb-2" width="45"><br>
                                <div class="team-name">${match.teams.away.name}</div>
                                <div class="goal-scorers">${awayGoalsHtml}</div>
                            </div>
                        </div>
                        <div class="status">Status: ${status}</div>
                        <div class="timer" id="timer-${index}">${elapsed} min</div>
                    </div>
                </div>`;
                });
            }
            $('#matches').html(html);
        }
        // Fetch fixtures
        function fetchFixtures() {
            $.get('{{ url('/football/fixtures-api') }}', function(data) {
                fixturesData = data.response || data; // Adjust to your API
                renderFixtures();
            });
        }

        function renderFixtures() {
            let html = '';
            if (!fixturesData.length) {
                html = '<div class="alert alert-info text-center">No upcoming fixtures available.</div>';
            } else {
                fixturesData.forEach(fix => {
                    let date = new Date(fix.fixture.date);
                    let localTime = date.toLocaleString('en-IN', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    });

                    // Goals (show only if match finished or in progress)
                    let homeGoals = fix.goals.home !== null ? fix.goals.home : '-';
                    let awayGoals = fix.goals.away !== null ? fix.goals.away : '-';

                    // Status label
                    let statusLabel = fix.fixture.status.short;
                    if (statusLabel === 'FT') {
                        statusLabel = 'Finished';
                    } else if (statusLabel === 'NS') {
                        statusLabel = 'Not Started';
                    }

                    html += `
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="fixture-card text-center p-3 shadow-sm rounded">
                        <div class="league mb-2 d-flex align-items-center justify-content-center">
                            <img src="${fix.league.logo}" alt="${fix.league.name}" class="me-2" width="25">
                            <span class="fw-semibold">${fix.league.name}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center my-3">
                            <div class="text-center flex-fill">
                                <img src="${fix.teams.home.logo}" class="team-logo mb-2" width="45"><br>
                                <div class="team-name">${fix.teams.home.name}</div>
                                <div class="team-goals fw-bold">${homeGoals}</div>
                            </div>
                            <div class="vs fw-bold">VS</div>
                            <div class="text-center flex-fill">
                                <img src="${fix.teams.away.logo}" class="team-logo mb-2" width="45"><br>
                                <div class="team-name">${fix.teams.away.name}</div>
                                <div class="team-goals fw-bold">${awayGoals}</div>
                            </div>
                        </div>
                        <div class="match-date text-muted">${localTime}</div>
                        <div class="match-status mt-1 fw-semibold">${statusLabel}</div>
                    </div>
                </div>`;
                });
            }
            $('#fixtures-list').html(html);
        }

        // Timer update
        function updateTimers() {
            matchesData.forEach((match, index) => {
                if (['1H', '2H', 'ET'].includes(match.fixture.status.short)) {
                    match.fixture.status.elapsed = (match.fixture.status.elapsed ?? 0) + 1;
                    $('#timer-' + index).text(match.fixture.status.elapsed + ' min');
                }
            });
        }

        // Initial load
        fetchLiveMatches();
        fetchFixtures();
        setInterval(fetchLiveMatches, 60000);
        setInterval(updateTimers, 60000);
    </script>
</body>

</html>
