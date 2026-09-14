<?php
$raceName = $raceName ?? 'Příž - Nice';
$raceId = $raceId ?? 124;
 
$years = $years ?? [
    [
        'year' => 2024,
        'date_from' => '2024-06-12',
        'date_to' => '2024-06-16',
        'total_km' => 744.8,
        'stages' => [
            [
                'stage_order' => 1,
                'date' => '2024-06-12',
                'distance_km' => 172.4,
                'elevation' => 1960,
                'type' => 'Plain stage',
                'winner' => 'L. Varga',
                'stage_ranking_url' => '/rocnik/2024/etapa/1/poradi',
                'overall_ranking_url' => '/rocnik/2024/etapa/1/poradi-po-etape',
            ],
            [
                'stage_order' => 2,
                'date' => '2024-06-13',
                'distance_km' => 154.9,
                'elevation' => 2125,
                'type' => 'Mountain stage',
                'winner' => 'T. Novak',
                'stage_ranking_url' => '/rocnik/2024/etapa/2/poradi',
                'overall_ranking_url' => '/rocnik/2024/etapa/2/poradi-po-etape',
            ],
        ],
    ],
    [
        'year' => 2023,
        'date_from' => '2023-06-08',
        'date_to' => '2023-06-12',
        'total_km' => 687.1,
        'stages' => [
            [
                'stage_order' => 1,
                'date' => '2023-06-08',
                'distance_km' => 163.9,
                'elevation' => 1750,
                'type' => 'Flat stage',
                'winner' => 'J. Kovář',
                'stage_ranking_url' => '/rocnik/2023/etapa/1/poradi',
                'overall_ranking_url' => '/rocnik/2023/etapa/1/poradi-po-etape',
            ],
        ],
    ],
];
 
$raceOptions = $raceOptions ?? [
    ['id' => 1, 'name' => 'Příž - Nice 2024'],
    ['id' => 2, 'name' => 'Příž - Nice 2025'],
    ['id' => 3, 'name' => 'Příž - Nice 2026'],
    ['id' => 4, 'name' => 'Příž - Nice 2027'],
];
 
usort($years, static function ($a, $b) {
    return ($b['year'] ?? 0) <=> ($a['year'] ?? 0);
});
 
foreach ($years as &$year) {
    if (!isset($year['stages'])) {
        $year['stages'] = [];
    }
 
    usort($year['stages'], static function ($a, $b) {
        return ($a['stage_order'] ?? 0) <=> ($b['stage_order'] ?? 0);
    });
}
unset($year);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($raceName) ?> | Ročníky</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
            margin: 0;
            padding: 32px;
        }
 
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
 
        .header-box,
        .year-box,
        .form-box {
            background: #fff;
            border: 1px solid #dfe3ec;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }
 
        .header-box {
            padding: 24px 28px;
            margin-bottom: 24px;
        }
 
        .header-box h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }
 
        .header-box .meta {
            color: #4b5563;
            font-size: 14px;
        }
 
        .year-box {
            padding: 22px 24px;
            margin-bottom: 20px;
        }
 
        .year-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
 
        .year-header h2 {
            margin: 0;
            font-size: 22px;
        }
 
        .year-meta {
            font-size: 14px;
            color: #4b5563;
        }
 
        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }
 
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #edf1f7;
            text-align: left;
            vertical-align: top;
        }
 
        th {
            background: #f8fafc;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
 
        td {
            font-size: 14px;
        }
 
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #075985;
            font-weight: 600;
            font-size: 12px;
        }
 
        .link-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
 
        .link-list a {
            color: #2563eb;
            text-decoration: none;
        }
 
        .link-list a:hover {
            text-decoration: underline;
        }
 
        .form-box {
            padding: 24px 28px;
            margin-top: 30px;
        }
 
        .form-box h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 22px;
        }
 
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
 
        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
 
        .field label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }
 
        .field input,
        .field select,
        .field button {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
 
        .field input[type="file"] {
            padding: 8px 10px;
            background: #fff;
        }
 
        .submit-wrap {
            margin-top: 18px;
        }
 
        .submit-wrap button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 12px 18px;
        }
 
        .submit-wrap button:hover {
            background: #1d4ed8;
        }
 
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
 
            .year-header {
                flex-direction: column;
                align-items: flex-start;
            }
 
            table, thead, tbody, th, td, tr {
                display: block;
            }
 
            thead {
                display: none;
            }
 
            tr {
                border: 1px solid #edf1f7;
                border-radius: 8px;
                margin-bottom: 12px;
                padding: 10px;
                background: #fff;
            }
 
            td {
                border: none;
                padding: 8px 0;
            }
 
            td::before {
                content: attr(data-label);
                display: block;
                font-weight: 700;
                font-size: 12px;
                color: #6b7280;
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-box">
            <h1><?= esc($raceName) ?></h1>
            <div class="meta">Závod ID: <?= esc($raceId) ?></div>
        </div>
 
        <?php if (empty($years)): ?>
            <div class="year-box">
                <p>Pro tento závod zatím nejsou k dispozici žádné ročníky.</p>
            </div>
        <?php else: ?>
            <?php foreach ($years as $year): ?>
                <?php
                $yearLabel = $year['year'] ?? date('Y', strtotime($year['date_from'] ?? 'now'));
                $dateFrom = !empty($year['date_from']) ? date('d.m.Y', strtotime($year['date_from'])) : '-';
                $dateTo = !empty($year['date_to']) ? date('d.m.Y', strtotime($year['date_to'])) : '-';
                $totalKm = isset($year['total_km']) ? (int) round((float) $year['total_km']) : 0;
                ?>
                <section class="year-box">
                    <div class="year-header">
                        <h2><?= esc($yearLabel) ?></h2>
                        <div class="year-meta">
                            <?= esc($dateFrom) ?> - <?= esc($dateTo) ?>
                            <span class="badge">Celkem: <?= esc($totalKm) ?> km</span>
                        </div>
                    </div>
 
                    <?php if (empty($year['stages'])): ?>
                        <p>Pro tento ročník nejsou k dispozici žádné etapy.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Pořadí etapy</th>
                                    <th>Datum etapy</th>
                                    <th>Délka etapy</th>
                                    <th>Převýšení</th>
                                    <th>Typ etapy</th>
                                    <th>Vítěz etapy</th>
                                    <th>Pořadí v etapě</th>
                                    <th>Pořadí po etapě</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($year['stages'] as $stage): ?>
                                    <?php
                                    $stageDate = !empty($stage['date']) ? date('d.m.Y', strtotime($stage['date'])) : '-';
                                    $stageKm = isset($stage['distance_km']) ? (float) $stage['distance_km'] : 0;
                                    $stageElevation = isset($stage['elevation']) ? (int) $stage['elevation'] : 0;
                                    $stageType = $stage['type'] ?? '—';
                                    $winner = $stage['winner'] ?? '—';
                                    ?>
                                    <tr>
                                        <td data-label="Pořadí etapy"><?= esc($stage['stage_order'] ?? '-') ?></td>
                                        <td data-label="Datum etapy"><?= esc($stageDate) ?></td>
                                        <td data-label="Délka etapy"><?= esc(number_format($stageKm, 1, ',', ' ')) ?> km</td>
                                        <td data-label="Převýšení"><?= esc($stageElevation) ?> m</td>
                                        <td data-label="Typ etapy"><?= esc($stageType) ?></td>
                                        <td data-label="Vítěz etapy"><?= esc($winner) ?></td>
                                        <td data-label="Pořadí v etapě">
                                            <?php if (!empty($stage['stage_ranking_url'])): ?>
                                                <div class="link-list">
                                                    <a href="<?= esc($stage['stage_ranking_url']) ?>" target="_blank" rel="noopener noreferrer">Zobrazit pořadí</a>
                                                </div>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Pořadí po etapě">
                                            <?php if (!empty($stage['overall_ranking_url'])): ?>
                                                <div class="link-list">
                                                    <a href="<?= esc($stage['overall_ranking_url']) ?>" target="_blank" rel="noopener noreferrer">Zobrazit pořadí</a>
                                                </div>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
 
        <div class="form-box">
            <h3>Přidat nový ročník závodu</h3>
 
            <form action="<?= site_url('admin/race-years/create') ?>" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="field">
                        <label for="real_name">Název ročníku</label>
                        <input type="text" id="real_name" name="real_name" placeholder="např. Příž - Nice 2025" required>
                    </div>
 
                    <div class="field">
                        <label for="race_id">Závod</label>
                        <select id="race_id" name="race_id" required>
                            <option value="">-- vyberte závod --</option>
                            <?php foreach ($raceOptions as $race): ?>
                                <option value="<?= esc($race['id']) ?>" <?= ($race['id'] == $raceId) ? 'selected' : '' ?>>
                                    <?= esc($race['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
 
                    <div class="field">
                        <label for="logo">Logo závodu</label>
                        <input type="file" id="logo" name="logo" accept="image/*">
                    </div>
                </div>
 
                <div class="submit-wrap">
                    <button type="submit">Uložit ročník</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
 