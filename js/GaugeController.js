function GaugeController(data, containerID) {
	
	this.topicID = data.topicID;
	this.containerID = containerID;
	
	this.gauge = null;
	
}

GaugeController.prototype = {
	
	draw: function() {
		
		this.gauge = $('#'+this.containerID).highcharts(Highcharts.merge(this.defaultOptions(), {
			yAxis: {
				min: topics[this.topicID].chartMin,
				max: topics[this.topicID].chartMax,
				title: {
					text: topics[this.topicID].description
				}
			},

			series: [{
				name: topics[this.topicID].units,
				data: [0],
				dataLabels: {
					format: '<div style="text-align:center"><span style="font-size:25px;color:' +
						((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y:.1f}</span><br/>' +
						   '<span style="font-size:12px;color:silver">'+topics[this.topicID].units+'</span></div>'
				},
				tooltip: {
					valueSuffix: topics[this.topicID].units
				}
			}]

		}));
		
	},
	
	update: function() {
		
		if (topics[this.topicID].latestPoint != null) {
			this.gauge.highcharts().series[0].points[0].update(topics[this.topicID].latestPoint.value);
		}
		
	},
	
	defaultOptions: function() {
		return {

        chart: {
            type: 'solidgauge',
			backgroundColor: 'rgba(255,255,255,0)'
        },
		
		credits: {
			enabled: false
		},

        title: null,

        pane: {
            center: ['50%', '85%'],
            size: '140%',
            startAngle: -90,
            endAngle: 90,
            background: {
                //backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || '#EEE',
                backgroundColor: 'rgba(255, 255, 255, 1)',
                innerRadius: '60%',
                outerRadius: '100%',
                shape: 'arc'
            }
        },

        tooltip: {
            enabled: false
        },

        // the value axis
        yAxis: {
			minColor: topics[this.topicID].colour,
			maxColor: topics[this.topicID].colour,
            lineWidth: 0,
            minorTickInterval: null,
            tickPixelInterval: 400,
            tickWidth: 0,
            title: {
                y: -70
            },
            labels: {
                y: 16
            }
        },

        plotOptions: {
            solidgauge: {
                dataLabels: {
                    y: 5,
                    borderWidth: 0,
                    useHTML: true
                }
            }
        }
    }
	}
	
}