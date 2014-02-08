function numOnly(evt)
{	
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if (charCode <= 13)
	{
		return true;
	}
	else
	{
		var keyChar = String.fromCharCode(charCode);
		var re = /[0-9]/
		return re.test(keyChar);
	}	
}

function realNumOnly(evt, number)
{	
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if (charCode <= 13)
	{
		return true;
	}
	else
	{
		var keyChar = String.fromCharCode(charCode);
		if(keyChar == '.'){
			var repunto = /[.]/
			return !repunto.test(number);
		}
		else{
			var re = /[0-9]/
			return re.test(keyChar);
		}
	}	
}

function alphaOnly(evt)
{	
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if (charCode <= 13)
	{
		return true;
	}
	else
	{
		var keyChar = String.fromCharCode(charCode);
		var re = /[a-zA-Z]/
		return re.test(keyChar);
	}	
}

function alphaNumOnly(evt)
{	
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if (charCode <= 13)
	{
		return true;
	}
	else
	{
		var keyChar = String.fromCharCode(charCode);
		var re = /[a-zA-Z0-9_-]/
		return re.test(keyChar);
	}	
}

function alphaNumSpaceOnly(evt)
{	
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if (charCode <= 13)
	{
		return true;
	}
	else
	{
		var keyChar = String.fromCharCode(charCode);
		var re = /[\sa-zA-Z0-9_-]/
		return re.test(keyChar);			
	}	
}

function noWhiteSpace(evt)
{	
	var charCode = (evt.which) ? evt.which : evt.keyCode;

	if (charCode <= 13)
	{
		return true;
	}
	else
	{
		var keyChar = String.fromCharCode(charCode);
		var re = /\s/ 
		return !re.test(keyChar);
	}	
}