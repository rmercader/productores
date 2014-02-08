// -----------------------------------------------
// Redondear un numero a la cantidad de decimales   
// -----------------------------------------------
function redondear(num,dec){
	num = parseFloat(num);
    dec = parseFloat(dec);
    dec = (!dec ? 2 : dec);
	return Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
}

// -----------------------------------------------
// Formatear un numero 1,111.56
// -----------------------------------------------
function formatFloat(num,dec){
	// Parte entera
	var Doy = "";
	var entera = num+"";
	entera = entera.substring(0,entera.indexOf('.'));	
	if (entera == '')
		entera =  num+""; 
	for (var j, i = entera.length - 1, j = 0; i >= 0; i--, j++)
		Doy = entera.charAt(i) + ((j > 0) && (j % 3 == 0)? ",": "") + Doy;
	
	// Decimales
	if (dec > 0){
		var ceros = "";
		for (var i=0; i<dec; i++) ceros += "0";	
		var txt = ""+num;	
		pos = txt.indexOf('.');	
		if (pos < 0){
			// No hay decimales
			txt = txt+"."+ceros;
		}
		else{
			// Hay decimales
			txt = txt+ceros;		
		}
		Doy = Doy+txt.substring(txt.indexOf('.'),txt.indexOf('.')+1+dec);
	}
	
	// Devuelvo
	return Doy;
}

// -----------------------------------------------
// Controla si es un integer
// -----------------------------------------------
function esInteger(numstr, AceptarNegativos) {
	// Devuelvo false si el Numero pasado es incorrecto
	if (numstr+"" == "undefined" || numstr+"" == "null" || numstr+"" == "")	
		return false;
	// Si el parametro AceptarBegativos es nulo Acepto
	if (AceptarNegativos+"" == "undefined" || AceptarNegativos+"" == "null")	
		AceptarNegativos = true;

	var Doy = true;
	// Convierto a string.
	numstr += "";	
	// Recorro caracteres para controlar
	for (i = 0; i < numstr.length; i++) {
    	if (!((numstr.charAt(i) >= "0") && (numstr.charAt(i) <= "9") || (numstr.charAt(i) == "-"))) {
       		Doy = false;
       		break;
		}
		else {
			if ((numstr.charAt(i) == "-" && i != 0) || 
				(numstr.charAt(i) == "-" && !AceptarNegativos)) {
				Doy = false;
       			break;
			}
		}
	}	         	        
   	return Doy;
}

// Controlo que el numero pasado sea Float
function esFloat(numstr) {
	// Devuelvo false si el Numero pasado es incorrecto
	if (numstr+"" == "undefined" || numstr+"" == "null" || numstr+"" == "")	
		return false;

	var Doy = true;
	var CanDec = 0;		// numero de decimales

	// Convierto a string
	numstr += "";	
	
	// Recorro caracteres para control
	for (i = 0; i < numstr.length; i++) {
		// track number of decimal points
		if (numstr.charAt(i) == ".")
			CanDec++;

    	if (!((numstr.charAt(i) >= "0") && (numstr.charAt(i) <= "9") || 
				(numstr.charAt(i) == "-") || (numstr.charAt(i) == "."))) {
			Doy = false;
       		break;
		}
		else {
			if ((numstr.charAt(i) == "-" && i != 0) ||
				(numstr.charAt(i) == "." && numstr.length == 1) ||
	    		(numstr.charAt(i) == "." && CanDec > 1)) {
       			Doy = false;
				break;
			}         	         	       
		}
   }
  return Doy;
}

function esFloatPos(oTxt,defecto){
	if (!(esFloat(oTxt.value) && parseFloat(oTxt.value) >= 0)) {
		oTxt.value = defecto;
		oTxt.focus();
		return false;
	}	
	return true;
}

function trim(cadena){
	for(i=0; i<cadena.length; )
	{
		if(cadena.charAt(i)==" ")
			cadena=cadena.substring(i+1, cadena.length);
		else
			break;
	}

	for(i=cadena.length-1; i>=0; i=cadena.length-1)
	{
		if(cadena.charAt(i)==" ")
			cadena=cadena.substring(0,i);
		else
			break;
	}
	
	return cadena;
}

function checkEmail(email) {
	var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return filter.test(email);
}