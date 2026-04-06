<?php

declare(strict_types=1);

function default_home_content(): array
{
    return [
        'hero_title' => 'Рекламное агентство полного цикла',
        'hero_text' => 'Мы предлагаем свои производственные и рекламные услуги на территории всей Владимирской области',
        'services_title' => 'Полный спектр услуг',
        'services_subtitle' => 'Услуги рекламного агентства покрывают почти все возможные потребности',
        'highlights_title' => 'Действуем в интересах клиента',
        'highlight_1' => 'ВСЕГДА НАЦЕЛЕНЫ НА КАЧЕСТВО',
        'highlight_2' => 'ОПЕРАТИВНОЕ ИЗГОТОВЛЕНИЕ',
        'highlight_3' => 'НАХОДИМСЯ ПРЯМО В ЦЕНТРЕ ГОРОДА',
        'portfolio_title' => 'Вот что мы сделали',
        'portfolio_subtitle' => 'Нашим ориентиром всегда было и остаётся качество',
        'footer_title' => 'Мы готовы решить вашу проблему',
        'footer_text' => 'Вам не обязательно ехать в офис рекламного агентства — можно оформить заказ дистанционно по удобному каналу связи',
    ];
}

function get_home_content(PDO $pdo): array
{
    $defaults = default_home_content();

    try {
        $stmt = $pdo->query('SELECT content_key, content_value FROM site_content');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        return $defaults;
    }

    foreach ($rows as $row) {
        $key = (string) ($row['content_key'] ?? '');
        if ($key !== '' && array_key_exists($key, $defaults)) {
            $defaults[$key] = (string) ($row['content_value'] ?? '');
        }
    }

    return $defaults;
}

function save_home_content(PDO $pdo, array $data): void
{
    $defaults = default_home_content();
    $allowedKeys = array_keys($defaults);

    $stmt = $pdo->prepare(
        'INSERT INTO site_content (content_key, content_value) VALUES (:key, :value)
         ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)'
    );

    foreach ($allowedKeys as $key) {
        if (!array_key_exists($key, $data)) {
            continue;
        }

        $value = trim((string) $data[$key]);
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
        ]);
    }
}
