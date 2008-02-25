function ajax(php, callback)
{
	var xmlHttp;
	try {
	  // Firefox, Opera 8.0+, Safari
	  xmlHttp=new XMLHttpRequest();
	}
	catch (e) {
	  // Internet Explorer
	  try {
	    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	  } catch (e) {
	      alert("Your browser does not support AJAX!");
	      return false;
	  }
	}
  
  xmlHttp.onreadystatechange=function() {
    if(xmlHttp.readyState==4) {
      if (callback != '') {
      	eval(callback+"('"+xmlHttp.responseText+"')");
      }
    }
  }
  xmlHttp.open("GET", php, true);
  xmlHttp.send(null);
}