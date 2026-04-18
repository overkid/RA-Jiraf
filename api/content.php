<?php

declare(strict_types=1);

function default_home_content(): array
{
    return [
        'hero_title' => 'Рекламное агентство полного цикла',
        'hero_text' => 'Мы предлагаем свои производственные и рекламные услуги на территории всей Владимирской области',
        'services_title' => 'Полный спектр услуг',
        'services_subtitle' => 'Услуги рекламного агентства покрывают почти все возможные потребности',
        'highlights_title' => 'Действуем в интересах клиента',
        'highlight_1' => 'Всегда нацелены на качество',
        'highlight_2' => 'Оперативное изготовление',
        'highlight_3' => 'Находимся прямо в центре города',
        'portfolio_title' => 'Вот что мы сделали',
        'portfolio_subtitle' => 'Нашим ориентиром всегда было и остаётся качество',
        'footer_title' => 'Мы готовы решить вашу проблему',
        'footer_text' => 'Вам не обязательно ехать в офис рекламного агентства - можно оформить заказ дистанционно по удобному каналу связи',
        'catalog_title' => 'Наши основные услуги',
        'catalog_subtitle' => 'Услуги рекламного агентства покрывают почти все возможные потребности',
        'catalog_help_title' => 'Не нашли нужную услугу?',
        'catalog_help_subtitle' => 'Свяжитесь с нами для уточнения',
        'nav_home_label' => 'Главная',
        'nav_services_label' => 'Услуги',
        'nav_contact_button' => 'Написать нам',
        'contact_button_text' => 'Связаться с нами',
        'manager_modal_title' => 'Заявка менеджеру',
        'manager_modal_text' => 'Мы свяжемся с вами для уточнения заказа и ответим на все ваши вопросы',
        'manager_name_label' => 'Представьтесь, пожалуйста',
        'manager_name_placeholder' => 'Ваше имя',
        'manager_phone_label' => 'Ваш номер телефона',
        'manager_service_label' => 'Услуга',
        'manager_service_placeholder' => 'Выберите услугу',
        'manager_service_other' => 'Другое',
        'manager_comment_label' => 'Комментарий к заявке или вопрос',
        'manager_submit_button' => 'Отправить',
        'manager_success_text' => 'Вы успешно отправили заявку, мы свяжемся с вами в скором времени',
        'service_modal_title' => 'Услуга',
        'service_modal_fallback_text' => 'Подробности по услуге уточняйте у менеджера',
        'service_modal_note' => 'Оставьте заявку, и менеджер подскажет сроки, материалы и точную стоимость под ваш тираж',
        'service_modal_contact_button' => 'Написать нам',
        'footer_email' => 'E-mail: giraf33@mail.ru',
        'footer_phone_1' => '8 (4922) 46-64-84',
        'footer_phone_2' => '8 (958) 510-64-84',
        'footer_address_title' => 'Офис находится по адресу:',
        'footer_address_line_1' => 'г. Владимир, ул. Ставровская, д. 4',
        'footer_address_line_2' => 'ост. 1001 мелочь, парковка рядом с домом',
    ];
}

function default_site_images(): array
{
    return [
        'services_card_1' => 'media/img/Visitka.png',
        'services_card_2' => 'media/img/ShirPechat.png',
        'services_card_3' => 'media/img/Stendi.png',
        'services_card_4' => 'media/img/Reklama.png',
        'portfolio_card_1' => 'media/img/Stakan.png',
        'portfolio_card_2' => 'media/img/KachestvVisit.png',
        'portfolio_card_3' => 'media/img/Knigi.png',
        'portfolio_card_4' => 'media/img/Stickers.png',
        'portfolio_card_5' => 'media/img/Brasleti.png',
        'portfolio_card_6' => 'media/img/Prints.png',
        'portfolio_card_7' => 'media/img/Diplomi.png',
        'portfolio_card_8' => 'media/img/Posters.png',
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

function get_site_images(PDO $pdo): array
{
    $defaults = default_site_images();

    try {
        $stmt = $pdo->query('SELECT content_key, content_value FROM site_content');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        return $defaults;
    }

    foreach ($rows as $row) {
        $key = (string) ($row['content_key'] ?? '');
        if ($key !== '' && array_key_exists($key, $defaults)) {
            $value = trim((string) ($row['content_value'] ?? ''));
            if ($value !== '') {
                $defaults[$key] = $value;
            }
        }
    }

    return $defaults;
}

function save_home_content(PDO $pdo, array $data): void
{
    save_content_by_whitelist($pdo, $data, array_keys(default_home_content()));
}

function save_site_images(PDO $pdo, array $data): void
{
    save_content_by_whitelist($pdo, $data, array_keys(default_site_images()));
}

function save_content_by_whitelist(PDO $pdo, array $data, array $allowedKeys): void
{
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

