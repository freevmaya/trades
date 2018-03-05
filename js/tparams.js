var tparams = {
	stop: {
		ctrls: ['value'],
		trigger: {
        	value: 0
    	}
	},
	limit: {
		ctrls: ['value'],
		trigger: {
        	value: 0
    	}
	},
	higner: {
		ctrls: ['value'],
		trigger: {
        	value: 0
    	}
	},
	below: {
		ctrls: ['value'],
		trigger: {
        	value: 0
    	}
	},
	candle: {
		ctrls: ['range', 'time'],
		trigger: {
        	range: {
        		k: "50%",
        		min: "-10%",
        		max: "10%"
        	},
        	time: 60 * 5
    	}
	},
	obalance: {
		ctrls: ['range_percent'],
		trigger: {
        	range_percent: {
        		min: -0.5,
        		max: 0.5
        	}
    	}
	},
	moods: {
		ctrls: ['range_percent'],
		trigger: {
        	range_percent: {
        		min: -0.5,
        		max: 0.5
        	}
    	}
	},
	floatLoss: {
		ctrls: ['value'],
		trigger: {
        	value: 0
    	}
	},
	window: {
		ctrls: ['cur_range'],
		trigger: {
        	cur_range: {
        		min: "curPrice - (minmax.max - minmax.min) * 0.3",
        		max: "curPrice + (minmax.max - minmax.min) * 0.3"
        	}
    	}
	}
}