function MeteogramController(data, containerID) {
	
	this.series = {
		temperature: null,
		pressure: null
	};
	
	for (var s in data.series) {
		this.series[data.series[s].seriesName] = new MeteogramSeries(data.series[s]);
	}
	
	this.chart = null;
	this.containerID = containerID;
	
}

MeteogramController.prototype = {
	
	draw: function() {
		//var meteogram = this;
		this.chart = new Highcharts.Chart(this.defaultOptions(), function (chart) {
			//meteogram.onChartLoad(chart);
		});
	},
	
	/**
	 * Updates the meteogram with data from the global topics array
	 */
	update: function() {
		
		var newTemps = topics[this.series.temperature.topicID].dataForScatterChart(this.series.temperature.latestPointTime);
		for (var n in newTemps) {
			this.chart.series[0].addPoint(newTemps[n], false, false);
			this.series.temperature.latestPointTime = Math.max(this.series.temperature.latestPointTime, moment(newTemps[n][0]).unix())
		}
		this.chart.redraw();
	},
	
	changePeriod: function(newPeriod) {
		
	},
	
	defaultOptions: function() {
		
		var meteogram = this;

		return {
			chart: {
				renderTo: this.containerID,
				marginBottom: 70,
				marginRight: 40,
				marginTop: 50,
				plotBorderWidth: 1,
				width: 800,
				height: 310
			},
			title: {
				text: 'Weather',
				align: 'left'
			},
			credits: {
				text: '',
				href: '',
				position: {
					x: -40
				}
			},
			tooltip: {
				shared: true,
				useHTML: true,
				//formatter: function () {
					//return meteogram.tooltipFormatter(this);
				//}
			},
			xAxis: [{// Bottom X axis
					type: 'datetime',
					tickInterval: 2 * 36e5, // two hours
					minorTickInterval: 36e5, // one hour
					tickLength: 0,
					gridLineWidth: 1,
					gridLineColor: (Highcharts.theme && Highcharts.theme.background2) || '#F0F0F0',
					startOnTick: false,
					endOnTick: false,
					minPadding: 0,
					maxPadding: 0,
					offset: 30,
					showLastLabel: true,
					labels: {
						format: '{value:%H}'
					}
				}, {// Top X axis
					linkedTo: 0,
					type: 'datetime',
					tickInterval: 24 * 3600 * 1000,
					labels: {
						format: '{value:<span style="font-size: 12px; font-weight: bold">%a</span> %b %e}',
						align: 'left',
						x: 3,
						y: -5
					},
					opposite: true,
					tickLength: 20,
					gridLineWidth: 1
				}],
			yAxis: [{// temperature axis
					title: {
						text: null
					},
					labels: {
						format: '{value}°',
						style: {
							fontSize: '10px'
						},
						x: -3
					},
					plotLines: [{// zero plane
							value: 0,
							color: '#BBBBBB',
							width: 1,
							zIndex: 2
						}],
					// Custom positioner to provide even temperature ticks from top down
					tickPositioner: function () {
						var max = Math.ceil(this.max) + 1,
								pos = max - 12, // start
								ret;

						if (pos < this.min) {
							ret = [];
							while (pos <= max) {
								ret.push(pos += 1);
							}
						} // else return undefined and go auto

						return ret;

					},
					maxPadding: 0.3,
					tickInterval: 1,
					gridLineColor: (Highcharts.theme && Highcharts.theme.background2) || '#F0F0F0'

				}, {// Air pressure
					allowDecimals: false,
					title: {// Title on top of axis
						text: 'hPa',
						offset: 0,
						align: 'high',
						rotation: 0,
						style: {
							fontSize: '10px',
							color: Highcharts.getOptions().colors[2]
						},
						textAlign: 'left',
						x: 3
					},
					labels: {
						style: {
							fontSize: '8px',
							color: Highcharts.getOptions().colors[2]
						},
						y: 2,
						x: 3
					},
					gridLineWidth: 0,
					opposite: true,
					showLastLabel: false
				}],
			legend: {
				enabled: false
			},
			plotOptions: {
				series: {
					pointPlacement: 'between'
				}
			},
			series: [{
					name: 'Temperature',
					data: topics[this.series.temperature.topicID].dataForScatterChart(),
					type: 'spline',
					marker: {
						enabled: false,
						states: {
							hover: {
								enabled: true
							}
						}
					},
					tooltip: {
						valueSuffix: '°C'
					},
					zIndex: 1,
					color: '#FF3333',
					negativeColor: '#48AFE8'
				}, {
					name: 'Air pressure',
					color: Highcharts.getOptions().colors[2],
					data: topics[this.series.pressure.topicID].dataForScatterChart(),
					marker: {
						enabled: false
					},
					shadow: false,
					tooltip: {
						valueSuffix: ' hPa'
					},
					dashStyle: 'shortdot',
					yAxis: 1
				}]
		};
		
	}
}

function MeteogramSeries(data) {
	
	this.name = data.seriesName;
	this.topicID = data.topicID;
	this.latestPointTime = 0;
	
}
