var locale = {
	SELECTTGTYPE: 'Выберите тип триггера',
	PRICE: 'цена',
	REMOVETRG: 'Удалить триггер исполнения',
	FROM: 'от',
	TO: 'до',
	PANIKACTIVATE: 'Паническая кнопка активирована!',
	BEGINTESTTITLE: 'Кнопка запускает тестовый проход, за выбранный на графике период',
	TIMEFORMAT: 'HH:mm',
	DATEFORMAT: 'd.M HH:mm',
	MESSAGE: 'Сообщение',
	WENTWRONG: 'Извините, но что то пошло не так.',
	PUSHAPPREQUIRE: 'Пожалуйста, что бы получать сигналы, сообщения о сделка и состоянии ордеров, разрешите получение сообщения в настройках браузера',
	VLINE: {
		buy: 'покупка',
		sell: 'мин.прод.',
	},
	ACTIONS: {
		buy: 'покупка',
		sell: 'продажа',
		message: 'сообщить'
	},
	TRIGGERS: {
		stop: 'цена',
		limit: 'разворот цены',
		higner: 'цена выше',
		below: 'цена ниже',
		candle: 'свеча',
		moods: 'настроение рынка',
		obalance: 'баланс ордеров',
		floatLoss: 'поднимающийся стоп-лосс',
		dev: 'пользовательские триггеры',
		window: 'окно'
	},
	TGSCTRLS: {
		stop: {value: {legend: 'Цена'}},
		limit: {value: {legend: 'Цена'}},
		higner: {value: {legend: 'Цена'}},
		below: {value: {legend: 'Цена'}},
		candle: {
			range: {legend: 'Интервал'},
			time: {legend: 'Время (сек.)'}
		},
		obalance: {range_percent: {'.caption': 'объемы пок-прод'}},
		moods: {range_percent: {'.caption': 'объемы пок-прод'}},
		floatLoss: {value: {legend: 'Процент просадки'}},
		window: {cur_range: {legend: 'Диапазон цены'}}
	}
}