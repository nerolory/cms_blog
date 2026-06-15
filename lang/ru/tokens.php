<?php

return [
    'page' => [
        'title' => 'Токены',
        'description' => 'Баланс токенов для заказа AI-анализа комментариев.',
        'balance' => 'Текущий баланс',
        'packages' => 'Пакеты токенов',
        'package_amount' => ':amount токенов',
        'package_price' => ':price :currency',
        'purchase_demo' => 'Купить',
        'demo_note' => 'Mock: charge + verified webhook. HTTP prod: токены только после webhook.',
        'no_packages' => 'Пакеты токенов пока не настроены.',
    ],

    'transactions' => [
        'purchase' => 'Покупка',
        'spend' => 'Списание',
        'refund' => 'Возврат',
        'admin_grant' => 'Начисление администратором',
    ],

    'messages' => [
        'purchased' => 'Зачислено :amount токенов.',
        'payment_pending' => 'Платёж принят. Токены зачислятся после подтверждения webhook.',
    ],

    'errors' => [
        'insufficient' => 'Недостаточно токенов: нужно :required, доступно :available.',
        'package_inactive' => 'Пакет токенов недоступен.',
        'gateway_unavailable' => 'Оплата временно недоступна.',
        'payment_failed' => 'Платёж не прошёл: :reason.',
        'payment_awaiting_webhook' => 'Ожидается подтверждение платежа через webhook.',
    ],
];
