var d = new Date();
var h = d.getHours();
var m = d.getMinutes();
var container = document.getElementById("current-hour");

container.innerHTML = h + ":" + m;