<?php

use App\Models\RaceModel;
use App\Models\RaceYearModel;
use App\Models\StageModel;
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
            'race_year' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, real_name TEXT NOT NULL, id_race INTEGER NOT NULL, year INTEGER NOT NULL, start_date TEXT NOT NULL, end_date TEXT NOT NULL, uci_tour INTEGER NOT NULL, logo TEXT, sex TEXT NOT NULL, category TEXT NOT NULL, country TEXT NOT NULL',
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

    public function testYearsAreNewestFirst(): void
    {
        $years = (new RaceYearModel($this->raceDb))->getYearsForRace(124);
        $this->assertSame([2024, 2023], array_map('intval', array_column($years, 'year')));
    }

    public function testStagesAreOrderedAndWinnerComesFromStageResults(): void
    {
        $stages = (new StageModel($this->raceDb))->getStagesForYear(10);
        $this->assertSame([1, 2], array_map('intval', array_column($stages, 'number')));
        $this->assertSame('Vítěz', $stages[0]['winner_first_name']);
        $this->assertNull($stages[1]['winner_first_name']);
        $this->assertSame(0, (int) $stages[0]['vertical_meters']);
        $this->assertNull((new StageModel($this->raceDb))->getStageForRace(101, 125));
    }

    public function testResultTypesAreSeparatedAndOrderedByRank(): void
    {
        $model = new StageModel($this->raceDb);
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
        $model = new RaceYearModel($this->raceDb);
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
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new DOMXPath($document);
        $this->assertSame('51 km', trim($xpath->query('//section[@id="rocnik-10"]//div[contains(@class,"h4")]')->item(0)->textContent));
        $this->assertSame(0, $xpath->query('//section[@id="rocnik-11"]//div[contains(@class,"h4")]')->length);
        $this->assertSame('', trim($xpath->query('//section[@id="rocnik-10"]//tbody/tr[2]/td[5]')->item(0)->textContent));
    }

    public function testParisNiceYearWithoutLogoAppearsInOverview(): void
    {
        $model = new RaceYearModel($this->raceDb);
        $id = $model->insert([
            'real_name' => 'Ročník bez loga', 'id_race' => 124, 'year' => 2025,
            'start_date' => '2025-03-01', 'end_date' => '2025-03-08', 'logo' => null,
            'sex' => 'M', 'category' => 'E', 'country' => 'cz', 'uci_tour' => 0,
        ]);
        $this->assertNotFalse($id);
        $this->assertNull($model->find($id)['logo']);

        $response = $this->get('/pariz-nice');
        $response->assertStatus(200);
        $html = $response->response()->getBody();
        $this->assertStringContainsString('Ročník bez loga', $html);
        $this->assertStringContainsString('id="rocnik-' . $id . '"', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    public function testFormShowsExpectedFieldsAndRaceOptions(): void
    {
        $response = $this->get('/race-years/create');
        $response->assertStatus(200);
        $html = $response->response()->getBody();
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new DOMXPath($document);
        $this->assertSame('number', $xpath->query('//input[@name="year"]')->item(0)->getAttribute('type'));
        $this->assertFalse($xpath->query('//input[@name="start_date"]')->item(0)->hasAttribute('min'));
        $this->assertFalse($xpath->query('//input[@name="logo"]')->item(0)->hasAttribute('maxlength'));
        $this->assertFalse($xpath->query('//input[@name="logo"]')->item(0)->hasAttribute('required'));
        $this->assertSame(2, $xpath->query('//select[@name="race_id"]/option')->length);
    }

    public function testResultPagesAndMissingResults(): void
    {
        $stage = $this->get('/pariz-nice/stage/101/results/1');
        $stage->assertStatus(200);
        $stageHtml = $stage->response()->getBody();
        $this->assertStringContainsString('fi-fr', $stageHtml);
        $this->assertStringContainsString('&lt;script&gt;etapy&lt;/script&gt;', $stageHtml);
        $overall = $this->get('/pariz-nice/stage/101/results/4');
        $overall->assertStatus(200);
        $overallHtml = $overall->response()->getBody();
        $this->assertStringContainsString('Průběžný Lídr', $overallHtml);
        foreach ([$stageHtml, $overallHtml] as $html) {
            $this->assertStringContainsString('01. 03. 2024', $html);
            $this->assertStringContainsString('30,4 km', $html);
            $this->assertStringContainsString('Rovina', $html);
            $this->assertStringContainsString('node_modules/flag-icons/css/flag-icons.min.css', $html);
            $document = new DOMDocument();
            @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new DOMXPath($document);
            $this->assertSame(4, $xpath->query('//thead/tr/th')->length);
            $this->assertSame('', trim($xpath->query('//tbody/tr[1]/td[2]')->item(0)->textContent));
        }
        $empty = $this->get('/pariz-nice/stage/100/results/4');
        $empty->assertStatus(200);
        $this->assertStringContainsString('nejsou v databázi dostupné výsledky', $empty->response()->getBody());
    }

    public function testFlagsUseCountryCodeAndUnknownCountryStaysEmpty(): void
    {
        $this->raceDb->table('rider')->where('id', 1)->update(['country' => 'CZ']);
        $this->assertStringContainsString('fi-cz', $this->get('/pariz-nice/stage/101/results/4')->response()->getBody());
        $this->raceDb->table('rider')->where('id', 1)->update(['country' => 'zz']);
        $html = $this->get('/pariz-nice/stage/101/results/4')->response()->getBody();
        $this->assertStringNotContainsString('fi-zz', $html);
        $this->assertStringNotContainsString('aria-label="Stát ZZ"', $html);
    }

    public function testInvalidDatesShowFormWithOriginalValuesWithoutSaving(): void
    {
        $response = $this->post('/race-years', [
            csrf_token() => csrf_hash(),
            'real_name' => 'Neuložený <ročník>', 'race_id' => 124, 'year' => 2025,
            'start_date' => '2025-03-08', 'end_date' => '2025-03-01',
        ]);
        $response->assertStatus(200);
        $html = $response->response()->getBody();
        $this->assertStringContainsString('Datum do nesmí být před datem od.', $html);
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new DOMXPath($document);
        $this->assertSame('Neuložený <ročník>', $xpath->query('//input[@name="real_name"]')->item(0)->getAttribute('value'));
        $this->assertSame('124', $xpath->query('//select[@name="race_id"]/option[@selected]')->item(0)->getAttribute('value'));
        $this->assertNull((new RaceYearModel($this->raceDb))->where('real_name', 'Neuložený <ročník>')->first());
    }

    public function testSubmittedFemaleRaceIsRejected(): void
    {
        $response = $this->post('/race-years', [
            csrf_token() => csrf_hash(),
            'real_name' => 'Nepovolený ročník', 'race_id' => 125, 'year' => 2025,
            'start_date' => '2025-03-01', 'end_date' => '2025-03-08',
        ]);
        $response->assertStatus(200);
        $this->assertStringContainsString('Vyberte mužský závod kategorie E.', $response->response()->getBody());
        $this->assertNull((new RaceYearModel($this->raceDb))->where('real_name', 'Nepovolený ročník')->first());
    }

    public function testSubmittedYearWithoutLogoIsSavedAndRedirectsToOverview(): void
    {
        $response = $this->post('/race-years', [
            csrf_token() => csrf_hash(),
            'real_name' => '  Uložený ročník  ', 'race_id' => 124, 'year' => 2025,
            'start_date' => '2025-03-01', 'end_date' => '2025-03-08',
        ]);
        $year = (new RaceYearModel($this->raceDb))->where('real_name', 'Uložený ročník')->first();
        $this->assertNotNull($year);
        $this->assertSame(124, (int) $year['id_race']);
        $this->assertNull($year['logo']);
        $response->assertRedirectTo(base_url('pariz-nice') . '#rocnik-' . $year['id']);
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
