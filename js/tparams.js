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
        		min: -10,
        		max: 10
        	},
        	time: 60 * 5
    	}
	},
	moods: {
		ctrls: ['range'],
		trigger: {
        	range: {
        		k: 0.01,
        		min: -1,
        		max: 1
        	}
    	}
	}
}