

function Topic(data) {
	this.id = data.id;
	this.name = data.name;
	this.description = data.description;
	this.units = data.units;
	
	this.chartType = data.chartType;
	this.colour = data.chartColour;
	this.chartMin = data.chartMin;
	this.chartMax = data.chartMax;
	this.decimalPoints = data.decimalPoints;
	this.accumulative = data.accumulative;
	
	this.mostRecent = moment().unix() - this.period;
	
	this.points = {};
	this.latestPoint = null;
}

Topic.prototype = {
	
	periodName: function() {
		if (this.period == 86400) {
			return '24hrs';
		} else if (this.period == 172800) {
			return '48hrs'
		} else if (this.period == 604800) {
			return 'week'
		}
	},
	
	/**
	 * 
	 * @param array pts
	 */
	addPoints: function(pts) {
		
		for (var p in pts) {
			
			var point = new Point(pts[p]);
			
			this.points[point.time.unix()] = point;
			
			if (this.latestPoint == null || point.time.isAfter(this.latestPoint.time)) {
				this.latestPoint = point;
				this.mostRecent = point.time.unix();
			}
		}
	},
	/**
	 * 
	 * @param moment time
	 */
	removeOldPoints: function() {
		var u = moment().unix() - 7*86400;
		
		for (var p in this.points) {
			if (p < u) {
				delete this.points[p];
			}
		}
	},
	
	
	/**
	 * returns an array of [<Date>, <value>] objects to use in a scatter chart
	 * 
	 * @param unixTime since
	 * @returns array
	 */
	dataForScatterChart: function(since) {
		if (Object.keys(this.points).length == 0) {
			/*this.points[0] = new Point({
				id: 0,
				time: new Date(0),
				value: 0
			});//*/
			//return [{x: new Date(0), y: 0}];
			return [];
		}
		
		since = (typeof since === 'undefined') ? 0 : since;
		
		var data = [];
		var u = moment().unix() - period;
		for (var p in this.points) {
			
			if (p < u || p < since) {
				continue;
			}
			
			data.push(this.points[p].asXY());
		}
		return data;
	},
	
	minMax: function(since, until) {
		
		since = typeof since !== 'undefined' ? since : moment().unix() - period;
		until = typeof until !== 'undefined' ? until : moment().unix();
		
		var ret = {
			minValue: Number.POSITIVE_INFINITY,
			minTime: moment(),
			maxValue: Number.NEGATIVE_INFINITY,
			maxTime: moment()
		};
		
		for (var p in this.points) {
			
			if (p < since || p > until) {
				continue;
			}
			
			if (this.points[p].value < ret.minValue) {
				ret.minValue = this.points[p].value;
				ret.minTime = this.points[p].time;
			}
			
			if (this.points[p].value > ret.maxValue) {
				ret.maxValue = this.points[p].value;
				ret.maxTime = this.points[p].time;
			}
			
		}
		
		return ret;
	},
	
	total: function(since, until) {
		since = typeof since !== 'undefined' ? since : moment().unix() - period;
		until = typeof until !== 'undefined' ? until : moment().unix();
		
		var ret = 0;
		
		for (var p in this.points) {
			
			if (parseInt(p) < since || parseInt(p) > until) {
				continue;
			}
			
			ret += this.points[p].value;
			
		}
		
		return ret;
	}
	
}

function Point(data) {
	this.id = data.id;
	this.time = moment(data.time);
	this.value = data.value;
}

Point.prototype = {
	asXY: function() {
		return {
			x: this.time.toDate(),
			y: this.value
		};
	}
}