<?php
return [
    'categories' => [
        'face_shape'  => ['label' => 'Face',        'count' => 5, 'nullable' => false, 'colorField' => 'skin_color'],
        'hair'        => ['label' => 'Hair',        'count' => 8, 'nullable' => false, 'colorField' => 'hair_color'],
        'eyebrows'    => ['label' => 'Eyebrows',    'count' => 6, 'nullable' => false, 'colorField' => null],
        'eyes'        => ['label' => 'Eyes',        'count' => 8, 'nullable' => false, 'colorField' => 'eye_color'],
        'nose'        => ['label' => 'Nose',        'count' => 6, 'nullable' => false, 'colorField' => null],
        'mouth'       => ['label' => 'Mouth',       'count' => 8, 'nullable' => false, 'colorField' => null],
        'facial_hair' => ['label' => 'Facial hair', 'count' => 5, 'nullable' => true,  'colorField' => null],
        'glasses'     => ['label' => 'Glasses',     'count' => 5, 'nullable' => true,  'colorField' => null],
        'shirt'       => ['label' => 'Shirt',       'count' => 8, 'nullable' => false, 'colorField' => 'shirt_color'],
    ],

    'palettes' => [
        'skin_color' => ['#FFE0BD', '#F2C396', '#E0AC7A', '#C68642', '#8D5524', '#5C3A21', '#3B2415', '#F5D5C0'],
        'hair_color' => ['#1B1B1B', '#3B2314', '#6A4028', '#A5693C', '#D9B36C', '#E8C25A', '#B33F3F', '#8A8A8A', '#F5F5F5', '#5C4EBF'],
        'eye_color'  => ['#4A3728', '#2E5339', '#355C8A', '#6B6B6B', '#7A4B2E', '#1E1E1E', '#4F8FD1', '#8A6D3B'],
        'shirt_color'=> ['#81B6FF', '#FFFFFF', '#FF6B6B', '#4CAF50', '#FFC94C', '#9B59B6', '#2C3E50', '#F39C12'],
    ],

    'defaults' => [
        'skin_color' => '#F2C396',
        'hair' => 1,
        'hair_color' => '#3B2314',
        'eyes' => 0,
        'eye_color' => '#4A3728',
        'eyebrows' => 2,
        'nose' => 0,
        'mouth' => 0,
        'facial_hair' => null,
        'face_shape' => 0,
        'glasses' => null,
        'shirt' => 0,
        'shirt_color' => '#81B6FF',
    ],
];
