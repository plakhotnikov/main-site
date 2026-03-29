<?php

$tour_types = [
    'cruise' => [
        'name' => 'Круиз',
        'price' => 2000,
        'description' => 'На большом теплоходе',
        'image' => '../../pic/cruise.jpg',
        'services_label' => 'Развлечения',
    ],
    'safari' => [
        'name' => 'Сафари',
        'price' => 3000,
        'description' => 'В жаркой пустыне',
        'image' => '../../pic/safari.jpeg',
        'services_label' => 'Экскурсии',
    ],
    'gastro' => [
        'name' => 'Гастротур',
        'price' => 1000,
        'description' => 'Этнические рестораны',
        'image' => '../../pic/gastro_tur.jpg',
        'services_label' => 'Места',
    ],
];

$meal_types = [
    'breakfast' => [
        'name' => 'Завтрак',
        'price' => 10,
        'time' => 'с 8-00 до 10-00',
    ],
    'dinner' => [
        'name' => 'Ужин',
        'price' => 20,
        'time' => 'с 19-00 до 22-00',
    ],
    'full' => [
        'name' => 'Пансион',
        'price' => 50,
        'time' => 'добавляется обед с 13-00 до 15-00',
    ],
];

$countries = [
    'cruise' => [
        ['name' => 'Италия', 'surcharge' => 200],
        ['name' => 'Хорватия', 'surcharge' => 100],
        ['name' => 'Швеция', 'surcharge' => 300],
    ],
    'safari' => [
        ['name' => 'Кения', 'surcharge' => 500],
        ['name' => 'Марокко', 'surcharge' => 300],
        ['name' => 'ЮАР', 'surcharge' => 800],
    ],
    'gastro' => [
        ['name' => 'Дания', 'surcharge' => 50],
        ['name' => 'Норвегия', 'surcharge' => 100],
        ['name' => 'Франция', 'surcharge' => 80],
    ],
];

$services = [
    'cruise' => [
        ['name' => 'Сауна', 'price' => 50],
        ['name' => 'Бассейн', 'price' => 100],
        ['name' => 'Бар', 'price' => 200],
    ],
    'safari' => [
        ['name' => 'Кормление животных', 'price' => 100],
        ['name' => 'Фотоохота', 'price' => 50],
        ['name' => 'Разделывание туши', 'price' => 200],
    ],
    'gastro' => [
        ['name' => 'Местный рынок', 'price' => 50],
        ['name' => 'Приготовление еды', 'price' => 200],
        ['name' => 'Виноферма', 'price' => 100],
    ],
];
