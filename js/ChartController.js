function ChartController(data) {
	
	this.chart = null;
	this.context = null;
	
	this.topicID = data.topicID;
	
	this.latestPointTime = 0;
	
}

ChartController.prototype = {
	
	draw: function() {
		
		var topic = topics[this.topicID];
		
		var $canvas = $('<canvas>').addClass('').attr('id', 'canvas_' + topic.id);
		
		var $numbers = $('<h3>').addClass('vertical-center');
		
		if (topic.chartType == 'scatter') {
			
			$numbers.append(
				$('<span>').addClass('space-after').attr('id', 'current_' + topic.id),
				$('<small>').addClass('').append(
					$('<span>').addClass('space-after').attr('id', 'max24_'+topic.id).append(
						$('<span>').addClass('glyphicon glyphicon-triangle-top glyphicon-small')
					),
					$('<span>').addClass('').attr('id', 'min24_'+topic.id).append(
						$('<span>').addClass('glyphicon glyphicon-triangle-bottom glyphicon-small')
					)
				)
			);
			
		} else if (topic.chartType == 'bar') {
			
			$numbers.append(
				$('<span>').addClass('space-after').attr('id', 'total_' + topic.id)
			);
			
		}
		
		$('#placeForCharts').append(
			//$('<li>').append(
			$('<div>').addClass('col-md-6').css({
				padding: '10px'
			}).append(
				$('<div>').addClass('').css({
					backgroundColor: topic.colour,
					borderRadius: '3px',
					border: 'none',
					padding: '0',
					zIndex: '100'
				}).append(
					
					$('<div>').addClass('summary').append(
						$('<span>').css({
							color: 'rgba(255,255,255,0.65)',
							textTransform: 'uppercase'
						}).text(topic.description),
						$('<span>').addClass('pull-right hidden-xs').css({
							color: 'rgba(255,255,255,0.65)'
						}).attr('id', 'lastUpdated_' + topic.id),
						$('<span>').addClass('pull-right visible-xs-inline').css({
							color: 'rgba(255,255,255,0.65)'
						}).attr('id', 'lastUpdatedShort_' + topic.id),
						$('<br>'),
						$numbers
					),
					$('<div>').addClass('chart').append($canvas)
					//$('<div>').addClass('lastUpdated').attr('id', 'lastUpdated_' + topic.id)
				)
			)
		);

		this.context = document.getElementById("canvas_" + topic.id).getContext("2d");

		this.chart = new Chart(this.context).Scatter([
			{
				label: topic.description,
				strokeColor: topic.colour,
				pointColor: topic.colour,
				pointStrokeColor: '#fff',
				data: [{x: 0, y: 0}],
				fill: true
			}
		], {
			bezierCurve: true,
			datasetStroke: true,
			pointDot: false,
			showTooltips: true,
			scaleShowHorizontalLines: true,
			scaleShowLabels: true,
			scaleType: "date",
			//scaleLabel: "<%=round(value, " + topic.decimalPoints + ")%>" + topic.units,
			scaleLabel: "<%=value%>" + topic.units,
			useUtc: false,
			scaleDateFormat: "mmm d",
			scaleTimeFormat: "HH:MM",
			scaleDateTimeFormat: "mmm d, yyyy, HH:MM",
			animation: false,
			responsive: true,
			maintainAspectRatio: false,
			pointHitDetectionRadius: 1,
			fill: true
		});
	},
	
	update: function() {
		//add more data from the topic
		
		var newTemps = topics[this.topicID].dataForScatterChart(this.latestPointTime);
		for (var n in newTemps) {
			this.chart.datasets[0].addPoint(newTemps[n].x, newTemps[n].y);
			this.latestPointTime = Math.max(this.latestPointTime, moment(newTemps[n][0]).unix())
		}
		
		if (this.chart.datasets[0].points.length > 1 && this.chart.datasets[0].points[0].arg == 0) {
			this.chart.datasets[0].removePoint(0);
		}
		
		this.chart.update();
		
		$('#current_' + this.topicID).contents().filter(function () {
			return this.nodeType === 3; 
		}).remove();
		$('#current_' + this.topicID).text(round(topics[this.topicID].latestPoint.value, topics[this.topicID].decimalPoints) + topics[this.topicID].units);
		$('#lastUpdated_' + this.topicID).empty().text(topics[this.topicID].latestPoint.time.fromNow());
		$('#lastUpdatedShort_' + this.topicID).empty().text(moment().diff(topics[this.topicID].latestPoint.time, 'minutes') + 'm');
		
		
		var minMax = topics[this.topicID].minMax();
		
		$('#min24_' + this.topicID).contents().filter(function () {
			return this.nodeType === 3; 
		}).remove();
		$('#min24_' + this.topicID).prepend(
			round(minMax.minValue, topics[this.topicID].decimalPoints) + topics[this.topicID].units
		);

		$('#max24_' + this.topicID).contents().filter(function () {
			return this.nodeType === 3; 
		}).remove();
		$('#max24_' + this.topicID).prepend(
			round(minMax.maxValue, topics[this.topicID].decimalPoints) + topics[this.topicID].units
		);

		var total = topics[this.topicID].total();
		
		$('#total_' + this.topicID).contents().filter(function () {
			return this.nodeType === 3; 
		}).remove();
		$('#total_' + this.topicID).prepend(
			round(total, topics[this.topicID].decimalPoints) + topics[this.topicID].units
		);
		
	},
	
	redrawChart: function() {
		
		this.chart.datasets[0].points = [];
		
		var u = moment().unix() - period;

		for (var p in topics[this.topicID].points) {
			if (parseInt(p) < u) {
				continue;
			}

			this.chart.datasets[0].addPoint(topics[this.topicID].points[p].time.toDate(), topics[this.topicID].points[p].value)
		}
		
		this.chart.update();
	}
	
};