function abrirAnimacionPyS(){
	TINY.box.show({url:'productos.html', width:800, height:570, boxid: 'tinycontent', maskid: 'maskcontent', close: false, left: (screen.width/8), top: -20});
	var tinybox = document.getElementById("tinycontent");
	tinybox.style.backgroundColor = 'transparent';
	tinybox.style.border = 'none';
}

function navegarEvento(urlEvento){
	var lf = (screen.width/5);
	var tp = (screen.height/6);
	TINY.box.show({url: urlEvento, close: true, width: 667, height: 300, left: lf, top: tp});
}