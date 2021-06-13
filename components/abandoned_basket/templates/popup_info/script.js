$(function() {

	$('.abandoned-basket').on('click', closePopup);

	function closePopup(event) {

	    if (event.target == this
            || event.target.classList.contains('abandoned-basket__close')
	    ) {
	        $(this).remove();
        }
    }



});