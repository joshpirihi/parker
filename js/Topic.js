

function Topic(data) {
	this.id = data.id;
	this.name = data.name;
	this.description = data.description;
	this.units = data.units;
	
	this.chartType = 'scatter';
	this.colour = data.chartColour;
	this.period = data.defaultPeriod;
	
	this.mostRecent = moment().unix() - this.period;
	
	this.points = {};
	this.chart = null;
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
		
		var u = moment().unix() - this.period;
		
		for (var p in pts) {
			var point = new Point(pts[p]);
			
			if (point.time.unix() >= u) {
				var xy = point.asXY();
				this.chart.datasets[0].addPoint(xy.x, xy.y);
			}
			
			this.points[point.time.unix()] = point;
			if (this.latestPoint == null || point.time.isAfter(this.latestPoint.time)) {
				this.latestPoint = point;
			}
		}
		
		if (this.chart.datasets[0].points[0].arg == 0) {
			this.chart.datasets[0].removePoint(0);
		}
		
		this.chart.update();
	},
	/**
	 * 
	 * @param moment time
	 */
	removeOldPoints: function() {
		var u = moment().unix() - 7*86400;
		
		//console.log('Deleting points older than '+moment.unix(u).format());
		
		//if (this.latestPoint != null) {
		//	u = this.latestPoint.time.unix() - this.period;
		//}
		
		for (var p in this.points) {
			//console.log('Checking '+p+' against '+u);
			if (p < u) {
				//console.log('Deleting point from '+this.points[p].time.format());
				delete this.points[p];
			}
		}
		
		u = u * 1000;
		
		for (var p in this.chart.datasets[0].points) {
			if (this.chart.datasets[0].points[p].arg < u) {
				this.chart.datasets[0].removePoint(p);
			}
		}
		
		this.chart.update();
	},
	/**
	 * 
	 * @param canvasContext context
	 */
	createChart: function (context) {
		
		if (this.chartType == 'scatter') {
			this.chart = new Chart(context).Scatter([
				{
					label: this.description,
					strokeColor: this.colour,
					pointColor: this.colour,
					pointStrokeColor: '#fff',
					data: this.dataForScatterChart()
				}
			], {
				bezierCurve: false,
				datasetStroke: true,
				pointDot: false,
				showTooltips: true,
				scaleShowHorizontalLines: true,
				scaleShowLabels: true,
				scaleType: "date",
				scaleLabel: "<%=value%>" + this.units,
				useUtc: false,
				scaleDateFormat: "mmm d",
				scaleTimeFormat: "HH:MM",
				scaleDateTimeFormat: "mmm d, yyyy, HH:MM",
				animation: false,
				responsive: true,
				maintainAspectRatio: false
			});
		}
	},
	
	/**
	 * returns an array of {x: <>,y: <>} objects to use in a scatter chart
	 * 
	 * @returns array
	 */
	dataForScatterChart: function() {
		if (Object.keys(this.points).length == 0) {
			/*this.points[0] = new Point({
				id: 0,
				time: new Date(0),
				value: 0
			});//*/
			return [{x: new Date(0), y: 0}];
		}
		var data = [];
		var u = moment().unix() - this.period;
		for (var p in this.points) {
			
			if (p < u) {
				continue;
			}
			
			data.push(this.points[p].asXY());
		}
		return data;
	},
	
	minMax: function(since, until) {
		
		since = typeof since !== 'undefined' ? since : 0;
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
	
	/**
	 * Clears and reassigns the chart dataset
	 */
	redrawChart: function() {
		
		//for (var i in this.chart.datasets[0].points) {
		//	this.chart.datasets[0].removePoint(i);
		//}
		
		this.chart.datasets[0].points = [];
		
		
		if (this.chartType == 'scatter') {
			
			var u = moment().unix() - this.period;
			
			/*var dataset = {
				label: this.description,
				strokeColor: this.colour,
				pointColor: this.colour,
				pointStrokeColor: '#fff',
				data: this.dataForScatterChart()
			};
			
			this.chart.datasets.push(dataset);
			this.chart.datasets.shift();*/
			
			for (var p in this.points) {
				if (p < u) {
					continue;
				}
				
				this.chart.datasets[0].addPoint(this.points[p].time.toDate(), this.points[p].value)
			}
			
		}
		
		this.chart.update();
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