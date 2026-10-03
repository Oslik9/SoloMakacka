<?php

use App\Models\RaceModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class RacesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private BaseConnection $raceDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->raceDb = db_connect('tests');
        $prefix = $this->raceDb->getPrefix();

        // Stejné názvy sloupců jako v dodaném SQL, bez časových razítek.
        $schemas = [
            'race' => 'id INTEGER PRIMARY KEY, default_name TEXT NOT NULL, link TEXT NOT NULL, country TEXT NOT NULL, type TEXT NOT NULL',
            'race_year' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, real_name TEXT NOT NULL, id_race INTEGER NOT NULL, year INTEGER NOT NULL, start_date TEXT NOT NULL, end_date TEXT NOT NULL, uci_tour INTEGER NOT NULL, logo TEXT NOT NULL, sex TEXT NOT NULL, category TEXT NOT NULL, country TEXT NOT NULL',
            'stage' => 'id INTEGER PRIMARY KEY, number INTEGER, date TEXT NOT NULL, note TEXT NOT NULL, departure TEXT NOT NULL, arrival TEXT NOT NULL, distance REAL NOT NULL, parcour_type INTEGER NOT NULL, vertical_meters INTEGER NOT NULL, profile TEXT NOT NULL, id_race_year INTEGER NOT NULL, link TEXT NOT NULL',
            'parcour_type' => 'id INTEGER PRIMARY KEY, name TEXT NOT NULL, icon TEXT NOT NULL',
            'rider' => 'id INTEGER PRIMARY KEY, first_name TEXT NOT NULL, last_name TEXT NOT NULL, country TEXT NOT NULL',
            'result' => 'id INTEGER PRIMARY KEY, id_stage INTEGER NOT NULL, id_rider INTEGER NOT NULL, rank INTEGER NOT NULL, type_result INTEGER NOT NULL, time TEXT, note TEXT NOT NULL',
        ];
        foreach ($schemas as $table => $columns) {
            $this->raceDb->query('CREATE TABLE ' . $prefix . $table . ' (' . $columns . ')');
        }

        $this->raceDb->table('race')->insertBatch([
            ['id' => 124, 'default_name' => 'Paris - Nice', 'link' => '', 'country' => 'fr', 'type' => ''],
            ['id' => 125, 'default_name' => 'Ženský závod', 'link' => '', 'country' => 'cz', 'type' => ''],
            ['id' => 126, 'default_name' => 'Závod U23', 'link' => '', 'country' => 'cz', 'type' => ''],
        ]);
        $years = [];
        foreach ([[10, 124, 2024, 'M', 'E'], [11, 124, 2023, 'M', 'E'], [12, 125, 2024, 'W', 'E'], [13, 126, 2024, 'M', 'U']] as [$id, $raceId, $year, $sex, $category]) {
            $years[] = [
                'id' => $id, 'id_race' => $raceId, 'real_name' => 'Ročník ' . $year,
                'year' => $year, 'start_date' => $year . '-03-01', 'end_date' => $year . '-03-08',
                'uci_tour' => 0, 'logo' => '', 'sex' => $sex, 'category' => $category, 'country' => 'fr',
            ];
        }
        $this->raceDb->table('race_year')->insertBatch($years);
        $stages = [];
        foreach ([[100, 2, 20.4], [101, 1, 30.4]] as [$id, $number, $distance]) {
            $stages[] = [
                'id' => $id, 'number' => $number, 'date' => '2024-03-0' . $number,
                'note' => '', 'departure' => 'Paříž', 'arrival' => 'Nice', 'distance' => $distance,
                'parcour_type' => 1, 'vertical_meters' => 0, 'profile' => '', 'id_race_year' => 10, 'link' => '',
            ];
        }
        $this->raceDb->table('stage')->insertBatch($stages);
        $this->raceDb->table('parcour_type')->insert(['id' => 1, 'name' => 'Rovina', 'icon' => '']);
        $this->raceDb->table('rider')->insertBatch([
            ['id' => 1, 'first_name' => 'Průběžný', 'last_name' => 'Lídr', 'country' => 'cz'],
            ['id' => 2, 'first_name' => 'Vítěz', 'last_name' => '<script>etapy</script>', 'country' => 'fr'],
        ]);
        $this->raceDb->table('result')->insertBatch([
            ['id' => 1, 'id_stage' => 101, 'id_rider' => 1, 'rank' => 2, 'type_result' => 1, 'time' => '01:02:00', 'note' => ''],
            ['id' => 2, 'id_stage' => 101, 'id_rider' => 2, 'rank' => 1, 'type_result' => 1, 'time' => '01:00:00', 'note' => ''],
            ['id' => 3, 'id_stage' => 101, 'id_rider' => 1, 'rank' => 1, 'type_result' => 4, 'time' => '04:00:00', 'note' => ''],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (['result', 'rider', 'parcour_type', 'stage', 'race_year', 'race'] as $table) {
            $this->raceDb->query('DROP TABLE ' . $this->raceDb->getPrefix() . $table);
        }
        parent::tearDown();
    }

    public function testYearsAreNewestFirstAndDistanceIsRounded(): void
    {
        $years = (new RaceModel($this->raceDb))->getYearsForRace(124);
        $this->assertSame([2024, 2023], array_map('intval', array_column($years, 'year')));
        $this->assertSame(51, (int) $years[0]['total_distance']);
        $this->assertSame(0, (int) $years[1]['total_distance']);
    }

    public function testStagesAreOrderedAndWinnerComesFromStageResults(): void
    {
        $stages = (new RaceModel($this->raceDb))->getStagesForYear(10);
        $this->assertSame([1, 2], array_map('intval', array_column($stages, 'number')));
        $this->assertSame('Vítěz', $stages[0]['winner_first_name']);
        $this->assertNull($stages[1]['winner_first_name']);
        $this->assertSame(0, (int) $stages[0]['vertical_meters']);
        $this->assertNull((new RaceModel($this->raceDb))->getStageForRace(101, 125));
    }

    public function testResultTypesAreSeparatedAndOrderedByRank(): void
    {
        $model = new RaceModel($this->raceDb);
        $stageResults = $model->getStageResults(101, 1);
        $this->assertSame([1, 2], array_map('intval', array_column($stageResults, 'rank')));
        $this->assertSame('Vítěz', $stageResults[0]['first_name']);
        $overall = $model->getStageResults(101, 4);
        $this->assertCount(1, $overall);
        $this->assertSame('Průběžný', $overall[0]['first_name']);
    }

    public function testDropdownContainsOnlyDistinctMaleEliteRaces(): void
    {
        $model = new RaceModel($this->raceDb);
        $this->assertSame([124], array_map('intval', array_column($model->getMaleCategoryERaces(), 'id')));
        $this->assertSame('fr', $model->getMaleCategoryERace(124)['country']);
        $this->assertNull($model->getMaleCategoryERace(125));
        $this->assertNull($model->getMaleCategoryERace(126));
    }

    public function testYearCanBeInsertedWithoutTimestampColumns(): void
    {
        $model = new RaceModel($this->raceDb);
        $id = $model->insert([
            'real_name' => 'Nový ročník', 'id_race' => 124, 'year' => 2025,
            'start_date' => '2025-03-01', 'end_date' => '2025-03-08', 'logo' => 'uploads/race-logos/test.png',
            'sex' => 'M', 'category' => 'E', 'country' => 'fr', 'uci_tour' => 0,
        ]);
        $this->assertNotFalse($id);
        $this->assertSame('Nový ročník', $model->find($id)['real_name']);
    }

    public function testOverviewUsesBootstrapAndEscapesRiderNames(): void
    {
        $response = $this->get('/pariz-nice');
        $response->assertStatus(200);
        $html = $response->response()->getBody();
        $this->assertStringContainsString('node_modules/bootstrap/dist/css/bootstrap.min.css', $html);
        $this->assertStringContainsString('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', $html);
        $this->assertStringContainsString('51 <span', $html);
        $this->assertStringContainsString('&lt;script&gt;etapy&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>etapy</script>', $html);
        $this->assertStringContainsString('stage/101/results/1', $html);
        $this->assertStringContainsString('stage/101/results/4', $html);
        $this->assertStringContainsString('Tento ročník zatím nemá žádné etapy.', $html);
    }

    public function testFormShowsExpectedFieldsAndRaceOptions(): void
    {
        $response = $this->get('/race-years/create');
        $response->assertStatus(200);
        $html = $response->response()->getBody();
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new DOMXPath($document);
        $this->assertSame('1800', $xpath->query('//input[@name="year"]')->item(0)->getAttribute('min'));
        $this->assertFalse($xpath->query('//input[@name="start_date"]')->item(0)->hasAttribute('min'));
        $this->assertFalse($xpath->query('//input[@name="logo"]')->item(0)->hasAttribute('maxlength'));
        $this->assertSame(2, $xpath->query('//select[@name="race_id"]/option')->length);
    }

    public function testResultPagesAndMissingResults(): void
    {
        $this->get('/pariz-nice/stage/101/results/1')->assertStatus(200);
        $overall = $this->get('/pariz-nice/stage/101/results/4');
        $overall->assertStatus(200);
        $this->assertStringContainsString('Průběžný Lídr', $overall->response()->getBody());
        $empty = $this->get('/pariz-nice/stage/100/results/4');
        $empty->assertStatus(200);
        $this->assertStringContainsString('nejsou v databázi dostupné výsledky', $empty->response()->getBody());
    }

    public function testUnsupportedResultTypeIsRejected(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->get('/pariz-nice/stage/101/results/2');
    }

    public function testUnknownStageIsRejected(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->get('/pariz-nice/stage/999/results/1');
    }
}
