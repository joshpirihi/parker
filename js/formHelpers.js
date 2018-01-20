
/**
 * Returns a jQuery object containing an input box with the supplied parameters
 * 
 * @returns 
 */
function inputBox(id, value) {
	
	return $('<input>').attr('name', id).attr('id', id).attr('value', value);
}

function checkBox(id, value) {
	return $('<input>').attr('type', 'checkbox').attr('id', id).attr('name', id).attr('checked', value==1);
}

function topicSelect(id, value) {
	
	$select = $('<select>').attr('id', id).attr('name', id);
	
	for (var t in topics) {
		$select.append($('<option>').attr('value', topics[t].id).attr('selected', (topics[t].id==value)).text(topics[t].description));
	}
	
	return $select;
}

/**
 * 
 * 
 * @param {type} fn
 * @returns {undefined}
 */
function getSafe(fn) {
    try {
        return fn();
    } catch (e) {
        return undefined;
    }
}