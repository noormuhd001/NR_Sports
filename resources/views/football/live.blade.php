<!DOCTYPE html>
<html>

<head>
    <title>NR Sports - Live Football Scores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f1f3f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: #1a1a2e;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .match-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 25px;
            transition: transform 0.2s;
        }

        .match-card:hover {
            transform: scale(1.02);
        }

        .league {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .league img {
            height: 40px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .team-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-bottom: 5px;
        }

        .team-name {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .score {
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0 20px;
        }

        .status {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .timer {
            font-size: 1rem;
            color: #dc3545;
            font-weight: bold;
            margin-top: 5px;
        }

        .goals {
            margin-top: 10px;
        }

        .goal-item {
            font-size: 0.85rem;
            margin: 2px 0;
        }

        .home-goals {
            text-align: left;
        }

        .away-goals {
            text-align: right;
        }

        .score-breakdown {
            font-size: 0.85rem;
            color: #555;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">NR Sports</a>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="mb-4 text-center">⚽ Live Football Scores</h2>
        <div id="matches" class="row justify-content-center"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let matchesData = [];

        function fetchLiveMatches() {
            $.ajax({
                url: '{{ url("/football/live-api") }}',
                method: 'GET',
                success: function (data) {
                    matchesData = data;
                    renderMatches();
                },
                error: function () {
                    $('#matches').html('<div class="alert alert-danger">Error fetching live matches.</div>');
                }
            });
        }

        function renderMatches() {
            let html = '';
            if (matchesData.length === 0) {
                html = '<div class="alert alert-info">No live matches right now.</div>';
            } else {
                matchesData.forEach((match, index) => {
                    let elapsed = match.fixture.status.elapsed ?? 0;
                    let status = match.fixture.status.short;

                    // Home and Away Goals
                    let homeGoals = '', awayGoals = '';
                    if (match.events && match.events.length > 0) {
                        match.events.forEach(ev => {
                            let timeText = ev.time.elapsed;
                            if (ev.time.extra) {
                                timeText += `+${ev.time.extra}`;
                            }
                            if (ev.type === "Goal") {
                                if (ev.team.id === match.teams.home.id) {
                                    homeGoals += `<div class="goal-item">${timeText}' ${ev.player.name}</div>`;
                                } else {
                                    awayGoals += `<div class="goal-item">${ev.player.name} ${timeText}'</div>`;
                                }
                            } else {
                                // Other events: card, substitution
                                let icon = '';
                                if (ev.type === 'Card') icon = '⚠️';
                                if (ev.type === 'Substitution') icon = '🔄';
                                if (ev.team.id === match.teams.home.id) {
                                    homeGoals += `<div class="goal-item">${icon} ${ev.player.name} ${timeText}'</div>`;
                                } else {
                                    awayGoals += `<div class="goal-item">${icon} ${ev.player.name} ${timeText}'</div>`;
                                }
                            }
                        });
                    }

                    // Score breakdown
                    let scoreBreakdown = `
                        <div class="score-breakdown">
                            HT: ${match.score.halftime.home ?? 0}-${match.score.halftime.away ?? 0} |
                            FT: ${match.score.fulltime.home ?? '-'}-${match.score.fulltime.away ?? '-'} |
                            ET: ${match.score.extratime.home ?? '-'}-${match.score.extratime.away ?? '-'}
                        </div>
                    `;

                    html += `
                        <div class="col-md-6 col-lg-5">
                            <div class="match-card text-center">
                                <div class="league">
                                    <img src="${match.league.logo}" alt="${match.league.name}"> ${match.league.name}
                                </div>
                                <div class="d-flex align-items-center justify-content-between my-3">
                                    <div class="text-center flex-fill">
                                        <img src="${match.teams.home.logo}" class="team-logo"><br>
                                        <div class="team-name">${match.teams.home.name}</div>
                                        <div class="goals home-goals">${homeGoals}</div>
                                    </div>
                                    <div class="score">${match.goals.home ?? 0} - ${match.goals.away ?? 0}</div>
                                    <div class="text-center flex-fill">
                                        <img src="${match.teams.away.logo}" class="team-logo"><br>
                                        <div class="team-name">${match.teams.away.name}</div>
                                        <div class="goals away-goals">${awayGoals}</div>
                                    </div>
                                </div>
                                <div class="status">Status: ${status}</div>
                                <div class="timer" id="timer-${index}">${elapsed} min</div>
                                ${scoreBreakdown}
                            </div>
                        </div>
                    `;
                });
            }
            $('#matches').html(html);
        }

        function updateLiveTimers() {
            matchesData.forEach((match, index) => {
                if (['1H', '2H', 'ET'].includes(match.fixture.status.short)) {
                    match.fixture.status.elapsed = (match.fixture.status.elapsed ?? 0) + 1;
                    $('#timer-' + index).text(match.fixture.status.elapsed + ' min');
                }
            });
        }

        // Initial fetch
        fetchLiveMatches();

        // Refresh API data every 60 seconds
        setInterval(fetchLiveMatches, 60000);

        // Update elapsed minutes every 60 seconds
        setInterval(updateLiveTimers, 60000);
    </script>
</body>

</html>
