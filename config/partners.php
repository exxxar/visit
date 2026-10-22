<?php
// config/partners.php
return [
    'levels' => [
        1 => ['name' => 'Новичок',       'min_orders' => 0,   'commission_pct' => 10],
        2 => ['name' => 'Опытный',       'min_orders' => 5,   'commission_pct' => 15],
        3 => ['name' => 'Эксперт',       'min_orders' => 20,  'commission_pct' => 20],
        4 => ['name' => 'Топ-партнер',   'min_orders' => 50,  'commission_pct' => 25],
    ],
    'mlm' => [
        'level_1' => 5, // 5% от дохода партнера 1-го уровня
        'level_2' => 3, // 3% от дохода партнера 2-го уровня
        'level_3' => 1, // 1% от дохода партнера 3-го уровня
    ],
];
