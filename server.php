<?php
// Заголовки для правильной работы
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$dataFile = 'data.json';
$authFile = 'auth.json';

// 1. Отдача данных для сайта
if ($action === 'getData') {
    if (file_exists($dataFile)) {
        echo file_get_contents($dataFile);
    } else {
        echo json_encode(["error" => "empty"]);
    }
    exit;
}

// 2. Проверка: установлен ли уже пароль?
if ($action === 'checkAuth') {
    echo json_encode(["isSetup" => file_exists($authFile)]);
    exit;
}

// Получаем данные от браузера
$input = json_decode(file_get_contents('php://input'), true);

// 3. Создание первого пароля
if ($action === 'setupAuth') {
    if (!file_exists($authFile)) {
        file_put_contents($authFile, json_encode([
            "login" => $input['login'],
            "hash" => $input['hash']
        ]));
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Уже настроено"]);
    }
    exit;
}

// 4. Проверка логина и пароля при входе в админку
if ($action === 'login') {
    if (file_exists($authFile)) {
        $auth = json_decode(file_get_contents($authFile), true);
        if ($auth['login'] === $input['login'] && $auth['hash'] === $input['hash']) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
    } else {
        echo json_encode(["success" => false]);
    }
    exit;
}

// 5. Сохранение настроек сайта
if ($action === 'saveData') {
    if (file_exists($authFile)) {
        $auth = json_decode(file_get_contents($authFile), true);
        if ($auth['login'] === $input['login'] && $auth['hash'] === $input['hash']) {
            file_put_contents($dataFile, json_encode($input['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Доступ запрещен"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Нет авторизации"]);
    }
    exit;
}
?>