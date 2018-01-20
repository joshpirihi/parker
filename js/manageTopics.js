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
			$('<th>').text('Chart Min'),
			$('<th>').text('Chart Max'),
			$('<th>').text('Decimal Points'),
			$('<th>').text('Order'),
			$('<th>').text('Acc.'),
			$('<th>')
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
				$('<td>').text(topics[t].colour),
				$('<td>').text(topics[t].chartMin),
				$('<td>').text(topics[t].chartMax),
				$('<td>').text(topics[t].decimalPoints),
				$('<td>').text(topics[t].order),
				$('<td>').text(topics[t].accumulative),
				$('<td>').append($('<input>').attr('type', 'button').attr('value', 'Edit').on('click', {"topicID": t}, editTopic))
			)
		);
		
	}
	
	$table.append($tbody);
	
	$('#placeForTopicsTable').empty().append($table);
	
	//also put a add new topic button at the bottom
	
	$('#placeForTopicsTable').append( $('<input>').attr('type', 'button').attr('value', 'Add new topic').on('click', editTopic) );
	
}

/**
 * Replaces the topics modal with an editor for a single topic.
 * If t is null then the fields are left empty and it's assumed they want to make a new one
 */
function editTopic(e) {
	
	t = getSafe(() => e.data.topicID);
	
	var $form = $('<form>').attr('id', 'editTopicForm').append($('<table>').append(
		$('<tr>').append(
			$('<td>').append('ID:'),
			$('<td>').append(inputBox('id', getSafe(() => topics[t].id)).attr('readonly', 'readonly'))
		),
		$('<tr>').append(
			$('<td>').text('MQTT Name:'),
			$('<td>').append(inputBox('name', getSafe(() => topics[t].name)))
		),
		$('<tr>').append(
			$('<td>').text('Description:'),
			$('<td>').append(inputBox('description', getSafe(() => topics[t].description)))
		),
		$('<tr>').append(
			$('<td>').text('Units:'),
			$('<td>').append(inputBox('units', getSafe(() => topics[t].units)))
		),
		$('<tr>').append(
			$('<td>').text('Chart Colour:'),
			$('<td>').append(inputBox('colour', getSafe(() => topics[t].colour)))
		),
		$('<tr>').append(
			$('<td>').text('Chart Min:'),
			$('<td>').append(inputBox('chartMin', getSafe(() => topics[t].chartMin)))
		),
		$('<tr>').append(
			$('<td>').text('Chart Max:'),
			$('<td>').append(inputBox('chartMax', getSafe(() => topics[t].chartMax)))
		),
		$('<tr>').append(
			$('<td>').text('Decimal Points:'),
			$('<td>').append(inputBox('decimalPoints', getSafe(() => topics[t].decimalPoints)))
		),
		$('<tr>').append(
			$('<td>').text('Order:'),
			$('<td>').append(inputBox('order', getSafe(() => topics[t].order)))
		),
		$('<tr>').append(
			$('<td>').text('Accumulative:'),
			$('<td>').append(inputBox('accumulative', getSafe(() => topics[t].accumulative)))
		),
		$('<tr>').append(
			$('<td>').attr('colspan', '2').append(
				$('<input>').attr('type', 'button').attr('value', 'Save').on('click', sendTopic)
			)
		)
		
	));
	
	$('#placeForTopicsTable').empty().append($form);
	
}


function sendTopic() {
	
	$.post('index.php?action=saveTopic', $('#editTopicForm').serialize(), function(data) {
		loadTopics();
		loadViews();
		$('#topicsModal').modal('hide');
	});
	
}
