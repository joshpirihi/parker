function ScatterChartController(data) {
	
	this.chart = null;
	this.context = null;
	
	this.topicID = data.topicID;
	
	this.latestPointTime = 0;
	
}

ScatterChartController.prototype = {
	
	draw: function() {
		
		var topic = topics[this.topicID];
		
		var $canvas = $('<canvas>').addClass('').attr('id', 'canvas_' + topic.id);

		$('#placeForCharts').append(
			$('<li>').append(
				$('<fieldset>').addClass('chartContainer').append(
					$('<legend>').css('text-align', 'center').text(topic.description),
					$('<table>').addClass('summary').append(
						$('<tr>').append(
							$('<td>').addClass('firstColumn'),
							$('<td>').addClass('secondColumn').attr('colspan', '2'), //.addClass('valueCell valueTitle').attr('id', 'minmax_'+t).text('Past '+topics[t].periodName()),
							$('<td>')
						),
						$('<tr>').append(
							$('<td>').addClass('valueCell').append(
								$('<div>').addClass('valueTitle').text('Current'),
								$('<div>').css('font-size', '150%').attr('id', 'current_' + topic.id)
							),
							$('<td>').addClass('valueCell').append(
								$('<div>').addClass('valueTitle').text('Min'),
								$('<div>').css('font-size', '100%').css('padding-top', '5px').attr('id', 'min24_' + topic.id)
							),
							$('<td>').addClass('valueCell').append(
								$('<div>').addClass('valueTitle').text('Max'),
								$('<div>').css('font-size', '100%').css('padding-top', '5px').attr('id', 'max24_' + topic.id)
							),
							$('<td>')
						)
					),
					$('<div>').addClass('chart').append($canvas),
					$('<div>').addClass('lastUpdated').attr('id', 'lastUpdated_' + topic.id)
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
				data: [{x: 0, y: 0}]
			}
		], {
			bezierCurve: false,
			datasetStroke: true,
			pointDot: false,
			showTooltips: true,
			scaleShowHorizontalLines: true,
			scaleShowLabels: true,
			scaleType: "date",
			//scaleLabel: "<%=round(value, " + topic.decimalPoints + "%>" + topic.units,
			scaleLabel: "<%=value%>" + topic.units,
			useUtc: false,
			scaleDateFormat: "mmm d",
			scaleTimeFormat: "HH:MM",
			scaleDateTimeFormat: "mmm d, yyyy, HH:MM",
			animation: false,
			responsive: true,
			maintainAspectRatio: false
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
		
		$('#current_' + this.topicID).empty().text(topics[this.topicID].latestPoint.value + topics[this.topicID].units);
		$('#lastUpdated_' + this.topicID).empty().text('Updated ' + topics[this.topicID].latestPoint.time.fromNow());

		var minMax = topics[this.topicID].minMax();
		$('#min24_' + this.topicID).empty().append(
			minMax.minValue + topics[this.topicID].units
		);
		$('#max24_' + this.topicID).empty().append(
			minMax.maxValue + topics[this.topicID].units
		);
		
	},
	
	redrawChart: function() {
		
		this.chart.datasets[0].points = [];
		
		var u = moment().unix() - period;

		for (var p in topics[this.topicID].points) {
			if (p < u) {
				continue;
			}

			this.chart.datasets[0].addPoint(topics[this.topicID].points[p].time.toDate(), topics[this.topicID].points[p].value)
		}
		
		this.chart.update();
	}
	
};