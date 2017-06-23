jQuery(document).ready(function($){
	console.log("wp-swift-form-builder x");

		// When a user leaves a form input
	// $('.js-form-control').blur(function(e){	
	$('body').on('blur', '.js-form-builder-control', function(e) {	
		var input = new FormBuilderInput($(this).serializeArray()[0]);
		console.log('blur');
		console.log(input);
		if(!input.isValid()) {
			
			// if(input.data_type !=='date_time') {
				// console.log(input.id+'-form-group');
				$(input.id+'-form-group').addClass('has-error');
				// console.log(input.id + ' has-error');
			// }
			// else {
			// 	if(!input.isValid()) {
			// 		$(input.id+'-form-group').addClass('has-error');
			// 		console.log(input.id + ' has-error');
			// 	}
			// }
		}
		else {
			$(input.id+'-form-group').addClass('has-success');
		}
	});


	$('body').on('focus', '.js-form-builder-control', function(e) {	
		$('#'+this.id+'-form-group').removeClass('has-error').removeClass('has-success');
	});

	//Form Input Object
	FormBuilderInput = function FormBuilderInput(input) {
		this.name = input.name;
		this.value = input.value;
		this.id = '#'+(this.name.replace(/[\[\]']+/g,''));
		this.required = $(this.id).prop('required');
		this.type = $(this.id).prop('type');
		this.data_type = $(this.id).data('type');
	};

	// Instance methods
	FormBuilderInput.prototype = {
	  errorCount: 0,
	  feedbackMessage: '', 
	  isValid: function isValid() {
	  	var re;
	  	if(this.required && this.value==='') {
	  		return false;
	  	}

	  	// console.log(this.id, ': ', this.data_type);
		switch (this.data_type) {
			case 'number':

			// console.log('isNaN(this.value)', isNaN(this.value));
				    return !isNaN(this.value);
		    case 'url':
		        re = /^(http(?:s)?\:\/\/[a-zA-Z0-9]+(?:(?:\.|\-)[a-zA-Z0-9]+)+(?:\:\d+)?(?:\/[\w\-]+)*(?:\/?|\/\w+\.[a-zA-Z]{2,4}(?:\?[\w]+\=[\w\-]+)?)?(?:\&[\w]+\=[\w\-]+)*)$/i;
		        return re.test(this.value);
		  	case 'email':
		      	re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		      	return re.test(this.value); 
		    case 'select':	
		    	return this.value.toLowerCase().substring(0, 6) !== 'select';	
		    case 'date_time':
		 //    	  				console.log('this.id', this.id);
			// console.log('this.data_type', this.data_type);
			// console.log('this.value', this.value);
		    	return isValidDateTime(this.value);   	
		    case 'date':
		    	return isValidDate(this.value); 
		    case 'password':
			    // console.log("password isValid...");
			   	return passwordCheck(this);
		    	// return false;
		    	// return isValidDate(this.value); 
		}
		return true;
	  }
	};	
});