<?
return array(

	'db' => array(
		'db.prod' => array(
			'provider' => 'mysql', // Провайдер БД (на текущий момент поддерживается только MySql)
			'host' => 'localhost', // Сервер БД
			'table' => 'mvc_test', // Имя базы данных
			'user' => 'root', // Пользователь
			'pass' => '', // Пароль
			'pref' => 'ar_base_', // ваш префикс для таблиц
			'character' => 'utf8' // Кодировка подключения
		)
	)
);
