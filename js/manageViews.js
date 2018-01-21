function fillViewsModal() {
	
	$('#viewToEdit').empty().append($('<option>').attr('value', '').attr('disabled', 'disabled').attr('selected', 'selected').text('Select...'));;
	
	for (var v in views) {
		$('#viewToEdit').append($('<option>').attr('value', v).text(views[v].name));
	}
	$('#viewToEdit').append($('<option>').attr('value', 'new').text('Add new...'));
	
}

function editView(vID) {
	
	//build a view editor.
	//a view has an ID, name, and an array of topic objects
	
	//list out all the topics
	topicRows = [];
	if (getSafe(() => views[vID]) != undefined) {
		for (var t in views[vID].viewTopics) {
			
			topicRows.push(viewTopicRow(views[vID].viewTopics[t]));
			
		}
	}
	
	$('#placeForViewsForm').empty().append(
		$('<form>').attr('id', 'editViewForm').append($('<table>').append(
			$('<tr>').append(
				$('<td>').text('ID:'),
				$('<td>').append(inputBox('viewID', getSafe(() => views[vID].id)).attr('readonly', true))
			),
			$('<tr>').append(
				$('<td>').text('Name:'),
				$('<td>').append(inputBox('viewName', getSafe(() => views[vID].name)))
			),
			$('<tr>').append(
				$('<td>').attr('colspan', '2').append(
					$('<table>').attr('id', 'topicRowsTable').append(
						$('<tr>').append(
							$('<th>').text('ID'),
							$('<th>').text('Topic'),
							$('<th>').text('Order'),
							$('<th>').text('Gauge'),
							$('<th>').text('Chart'),
							$('<th>').text('Big'),
							$('<th>').text('Delete')
						),
						topicRows,
						
					),
					$('<input>').attr('type', 'button').attr('value', 'Add topic').on('click', function(){ $('#topicRowsTable').append(viewTopicRow(null)) })
				)
			),
			$('<tr>').append(
				$('<td>').attr('colspan', 2).append($('<input>').attr('type', 'button').attr('value', 'Save').on('click', saveView))
			)
		)		
	));
	
}

function viewTopicRow(vt) {
	
	$row = $('<tr>').attr('data-name', 'topicRow').append(
		$('<td>').append(inputBox(null, getSafe(() => vt.id)).attr('data-name', 'id').attr('readonly', 'true')),
		$('<td>').append(topicSelect(null, getSafe(() => vt.topicID)).attr('data-name', 'topicID')),
		$('<td>').append(inputBox(null, getSafe(() => vt.order)).attr('data-name', 'order')),
		
		$('<td>').append(checkBox(null, getSafe(() => vt.gauge)).attr('data-name', 'gauge')),
		$('<td>').append(checkBox(null, getSafe(() => vt.chart)).attr('data-name', 'chart')),
		$('<td>').append(checkBox(null, getSafe(() => vt.big)).attr('data-name', 'big')),
		$('<td>').append(checkBox(null, false).attr('data-name', 'delete'))
	);
	
	return $row;
}

function saveView() {
	
	//build a nice object of the view.
	view = {
		'id': $('#viewID').val(),
		'name': $('#viewName').val(),
		'viewTopics': []
	};
	
	$('[data-name=topicRow]').each(function() {
		
		//create an object to add to the array
		
		var id = $(this).find('[data-name=id]')[0].value;
		
		//if (id != "" && id > 0) {
			view.viewTopics.push({
				'id': id,
				'topicID': $(this).find('[data-name=topicID]')[0].value,
				'order': $(this).find('[data-name=order]')[0].value,
				'gauge': $(this).find('[data-name=gauge]').is(':checked'),
				'chart': $(this).find('[data-name=chart]').is(':checked'),
				'big': $(this).find('[data-name=big]').is(':checked'),
				'delete': $(this).find('[data-name=delete]').is(':checked')
			});
		//}
		
	});
	
	console.log(view);
	
	$.post('index.php?action=saveView', view, function() {
		loadTopics();
		loadViews();
		$('#topicsModal').modal('hide');
	});
	
}