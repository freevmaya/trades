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
	WENTWRONG: 'Извините, но что то пошло не так.',
	VLINE: {
		buy: 'покупка',
		sell: 'мин.прод.',
	},
	ACTIONS: {
		buy: 'покупка',
		sell: 'продажа'
	},
	TRIGGERS: {
		stop: 'цена',
		limit: 'разворот цены',
		higner: 'цена выше',
		below: 'цена ниже',
		candle: 'свеча',
		moods: 'настроение рынка',
		obalance: 'баланс ордеров',
		dev: 'пользовательские триггеры'
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
		moods: {range: {'.caption': 'Баланс объемов покупок и продаж'}}
	}
}