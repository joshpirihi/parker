

function Topic(data) {
	this.id = data.id;
	this.name = data.name;
	this.description = data.description;
	this.units = data.units;
	this.points = {};
	this.chart = null;
	this.latestPoint = null;
}

Topic.prototype = {
	/**
	 * 
	 * @param array pts
	 */
	addPoints: function(pts) {
		for (var p in pts) {
			var point = new Point(pts[p]);
			var xy = point.asXY();
			point.chartPointIndex = this.chart.datasets[0].add(xy.x, xy.y);
			this.points[point.time.unix()] = point;
			if (this.latestPoint == null || point.time.isAfter(this.latestPoint.time)) {
				this.latestPoint = point;
			}
		}
		this.chart.update();
	},
	/**
	 * 
	 * @param moment time
	 */
	removePointsOlderThan: function(time) {
		var u = time.unix()
		for (var p in this.points) {
			if (p < u) {
				this.chart.datasets[0].removePoint(this.points[p].chartPointIndex);
				delete this.points[p];
			}
		}
		this.chart.update();
	},
	/**
	 * 
	 * @param canvasContext context
	 */
	createChart: function (context) {
		this.chart = new Chart(context).Scatter([
			{
				label: topics[t].description,
				strokeColor: '#F16220',
				pointColor: '#F16220',
				pointStrokeColor: '#fff',
				data: this.dataForScatterChart()
			}
		], {
			bezierCurve: true,
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
			animation: false
		});
	},
	
	/**
	 * returns an array of {x: <>,y: <>} objects to use in a scatter chart
	 * 
	 * @returns array
	 */
	dataForScatterChart: function() {
		var data = [];
		for (var p in this.points) {
			data.push(this.points[p].asXY());
		}
		return data;
	},
	
	minMax: function() {
		
		var ret = {
			minValue: Number.POSITIVE_INFINITY,
			minTime: moment(),
			maxValue: Number.NEGATIVE_INFINITY,
			maxTime: moment()
		};
		
		for (var p in this.points) {
			
			if (this.points[p].value < ret.minValue) {
				ret.minValue = this.points[p].value;
				ret.minTime = this.points[p].time;
			}
			
			if (this.points[p].value > ret.maxValue) {
				ret.maxValue = this.points[p].value;
				ret.maxTime = this.points[p].time;
			}
			
		}
		
	}
}

function Point(data) {
	this.id = data.id;
	this.time = moment.unix(data.time);
	this.value = data.value;
	this.chartPointIndex = null;
}

Point.prototype = {
	asXY: function() {
		var ret = {
			x: this.time.toDate(),
			y: this.value
		};
		return ret;
	}
}