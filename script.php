<?php

declare(strict_types=1);

/**
 * Генерирует расписание футбольного чемпионата на сезон, состоящий из двух кругов матчей.
 *
 * @param array $teams Массив футбольных команд, где каждая команда представлена в виде ассоциативного массива с ключом 'title' (название команды).
 *
 * @return array Массив сгенерированных матчей. Каждый матч представлен ассоциативным массивом с ключами:
 *               - 'date' (string): Дата матча
 *               - 'home' (string): Домашняя игра
 *               - 'away' (string): Игра в гостях
 *               - 'round' (int): Номер тура
 *               - 'circle' (int): Номер круга (1 или 2)
 */
function generateFootballSchedule(array $teams): array
{
    $numTeams = count($teams);
    $schedule = [];

    shuffle($teams);

    $alternating = [];
    for ($i = 0; $i < $numTeams; $i++) {
        $alternating[$i] = $i % 2 === 0 ? 'home' : 'away';
    }

    for ($circle = 1; $circle <= 2; $circle++) {
        for ($round = 0; $round < $numTeams - 1; $round++) {
            for ($i = 0; $i < $numTeams / 2; $i++) {
                $homeIndex = ($round + $i) % ($numTeams - 1);
                $awayIndex = ($numTeams - 1 - $i + $round) % ($numTeams - 1);

                if ($i == 0) {
                    $awayIndex = $numTeams - 1;
                }

                if ($circle == 2) {
                    $temp = $homeIndex;
                    $homeIndex = $awayIndex;
                    $awayIndex = $temp;
                }

                if ($alternating[$homeIndex] === 'home') {
                    $schedule[] = [
                        'date' => '',
                        'home' => $teams[$homeIndex]['title'],
                        'away' => $teams[$awayIndex]['title'],
                        'round' => $round + 1,
                        'circle' => $circle
                    ];
                } else {
                    $schedule[] = [
                        'date' => '',
                        'home' => $teams[$awayIndex]['title'],
                        'away' => $teams[$homeIndex]['title'],
                        'round' => $round + 1,
                        'circle' => $circle
                    ];
                }

                $alternating[$homeIndex] = $alternating[$homeIndex] === 'home' ? 'away' : 'home';
                $alternating[$awayIndex] = $alternating[$awayIndex] === 'home' ? 'away' : 'home';
            }
        }
    }

    return $schedule;
}

/**
 * Подготавливает данные расписания для отображения, разбивая матчи на круги и туры с указанием даты проведения.
 *
 * @param array $schedule Массив матчей, где каждый матч представлен ассоциативным массивом с ключами:
 *                        - 'date' (string): Дата матча
 *                        - 'home' (string): Домашняя игра
 *                        - 'away' (string): Игра в гостях
 *                        - 'round' (int): Номер тура
 *                        - 'circle' (int): Номер круга
 *
 * @return array Массив подготовленных данных для отображения расписания. Возвращает ассоциативный массив, где:
 *               - 'circle' (int): Номер круга (1 или 2).
 *               - 'rounds' (array): Массив туров, каждый тур представлен ассоциативным массивом с ключами:
 *                 - 'roundNumber' (int): Номер тура
 *                 - 'date' (string): Дата проведения тура
 *                 - 'matches' (array): Массив матчей в этом туре
 */
function prepareScheduleData(array $schedule): array
{
    $roundNumber = 1;
    $matchesByCircle = [];
    $lastDate = null;

    foreach ($schedule as $match) {
        $matchesByCircle[$match['circle']][] = $match;
    }

    $preparedData = [];

    foreach ($matchesByCircle as $circle => $matches) {
        $preparedData[$circle] = [
            'rounds' => [],
            'circle' => $circle,
        ];

        $matchesByRound = [];

        foreach ($matches as $match) {
            $matchesByRound[$match['round']][] = $match;
        }

        $secondCircleStartDate = null;

        foreach ($matchesByRound as $round => $roundMatches) {
            if ($circle == 1) {
                static $startDate;
                if (!$startDate) {
                    $startDate = strtotime("2024-11-23");
                }

                $date = date('d F Y', strtotime("+{$roundNumber} weeks", $startDate));

                if ($roundNumber == count($matchesByRound)) {
                    $lastDate = strtotime($date);
                }
            } else {
                if ($roundNumber == 1) {
                    $secondCircleStartDate = strtotime("+0 days", $lastDate);
                }

                $date = date('d F Y', strtotime("7 days +{$roundNumber} weeks", $secondCircleStartDate));
            }

            $preparedData[$circle]['rounds'][] = [
                'roundNumber' => $roundNumber,
                'date' => $date,
                'matches' => $roundMatches
            ];

            $roundNumber++;
        }
    }

    return $preparedData;
}

/**
 * Отображает HTML-расписание футбольных матчей.
 *
 * @param array $preparedData Массив данных, где каждый элемент представляет круг с турами и матчами:
 *                            - 'circle' (int): Номер круга (1 или 2).
 *                            - 'rounds' (array): Массив туров, каждый тур представлен ассоциативным массивом:
 *                              - 'roundNumber' (int): Номер тура.
 *                              - 'date' (string): Дата проведения тура.
 *                              - 'matches' (array): Массив матчей, каждый матч представлен ассоциативным массивом:
 *                                - 'home' (string): Название команды дома.
 *                                - 'away' (string): Название команды в гостях.
 *
 * @return void
 */
function displayScheduleHtml(array $preparedData): void
{
    echo '<div class="schedule">';

    foreach ($preparedData as $circleData) {
        echo "<h2>Круг {$circleData['circle']}</h2>";

        foreach ($circleData['rounds'] as $round) {
            echo "<h3>Тур {$round['roundNumber']}</h3>";
            echo '<table border="1" cellpadding="5" cellspacing="0">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Дата</th>';
            echo '<th>Хозяева</th>';
            echo '<th>Гости</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($round['matches'] as $match) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($round['date']) . '</td>';
                echo '<td>' . htmlspecialchars($match['home']) . '</td>';
                echo '<td>' . htmlspecialchars($match['away']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '<br>';
        }
    }

    echo '</div>';
}

// Пример входных данных
$jsonData = '{
    "teams": [
        { "id": 1, "title": "Ливерпуль" },
        { "id": 2, "title": "Челси" },
        { "id": 3, "title": "Тоттенхэм Хотспур" },
        { "id": 4, "title": "Арсенал" },
        { "id": 5, "title": "Манчестер Юнайтед" },
        { "id": 6, "title": "Эвертон" },
        { "id": 7, "title": "Лестер Сити" },
        { "id": 8, "title": "Вест Хэм Юнайтед" },
        { "id": 9, "title": "Уотфорд" },
        { "id": 10, "title": "Борнмут" },
        { "id": 11, "title": "Бернли" },
        { "id": 12, "title": "Саутгемптон" },
        { "id": 13, "title": "Брайтон энд Хоув Альбион" },
        { "id": 14, "title": "Норвич Сити" },
        { "id": 15, "title": "Шеффилд Юнайтед" },
        { "id": 16, "title": "Фулхэм" },
        { "id": 17, "title": "Сток Сити" },
        { "id": 18, "title": "Мидлсбро" },
        { "id": 19, "title": "Суонси Сити" },
        { "id": 20, "title": "Дерби Каунти" }
    ]
}';

/********************** Тест JSON-переменная ****************************/


$teams = json_decode($jsonData, true)['teams'];
$schedule = generateFootballSchedule($teams);
$preparedData = prepareScheduleData($schedule);
displayScheduleHtml($preparedData);


/********************** Тест JSON из файла ******************************/

/*
function loadJsonFromFile(string $filePath): array
{
    $jsonData = file_get_contents($filePath);
    return json_decode($jsonData, true)['teams'];
}

$teamsFromFile = loadJsonFromFile('teams.json');
$scheduleFromFile = generateFootballSchedule($teamsFromFile);
$preparedDataFromFile = prepareScheduleData($scheduleFromFile);
displayScheduleHtml($preparedDataFromFile);
*/

/*********************** Тест JSON по URL ******************************/

/*
function loadJsonFromUrl(string $url): array
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $jsonData = curl_exec($ch);
    curl_close($ch);

    return json_decode($jsonData, true)['teams'];
}

$teamsFromUrl = loadJsonFromUrl('https://gist.githubusercontent.com/Andrey-Yurchuk/f8a600ac4cf076502e211b35a0db3fd8/raw/9cd56012ede1bff04530691c054a2b1128511d22/teams.json');
$scheduleFromUrl = generateFootballSchedule($teamsFromUrl);
$preparedDataFromUrl = prepareScheduleData($scheduleFromUrl);
displayScheduleHtml($preparedDataFromUrl);
*/
