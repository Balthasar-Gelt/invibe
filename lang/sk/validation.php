<?php

return [
    'required' => 'Pole :attribute je povinné.',
    'unique'   => 'Hodnota :attribute už existuje.',
    'email'    => 'Pole :attribute musí byť platná e-mailová adresa.',
    'max'      => [
        'string' => 'Pole :attribute nemôže mať viac ako :max znakov.',
    ],
    'min'      => [
        'string' => 'Pole :attribute musí mať aspoň :min znakov.',
    ],

    'attributes' => [
        'name'  => 'názov',
        'email' => 'e-mail',
    ],
];
