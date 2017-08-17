function drawTopicsTable() {
	
	//headers
	var $table = $('<table>').addClass('table');
	
	$table.append($('<thead>').append(
		$('<tr>').append(
			$('<th>').text('ID'),
			$('<th>').text('MQTT Name'),
			$('<th>').text('Description'),
			$('<th>').text('Units'),
			$('<th>').text('Chart Colour'),
			$('<th>').text('Default Period'),
			$('<th>').text('Chart Min'),
			$('<th>').text('Chart Max'),
			$('<th>').text('Decimal Points'),
			$('<th>').text('Order'),
			$('<th>').text('Accumulative')
		)
	));
	
	//body
	var $tbody = $('<tbody>');
	
	for (var t in topics) {
		
		$tbody.append(
			$('<tr>').append(
				$('<td>').text(topics[t].id),
				$('<td>').text(topics[t].name),
				$('<td>').text(topics[t].description),
				$('<td>').text(topics[t].units),
				$('<td>').text(topics[t].id),
				$('<td>').text(topics[t].id),
				$('<td>').text(topics[t].id),
				$('<td>').text(topics[t].id),
			)
		);
		
	}
	
}

function addNewTopic() {
	
}

function sendTopics() {
	
}