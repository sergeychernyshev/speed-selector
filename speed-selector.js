/*
http://www.webpagetest.org/?iq=80

Script:
setViewportSize 1280 1024
navigate http://www.sergeychernyshev.com/

XML Result:
http://www.webpagetest.org/xmlResult/160301_W9_165/
*/

// [selector-boundaries]
var selectors = [
   '#photo img',
   '#title',
   '.section:nth-child(4)',
   '.section a:nth-child(2) img'
];

var viewport_width = document.documentElement.clientWidth;

var boundaries = viewport_width + ':';
selectors.map(function(selector) {
     var r = document.querySelector(selector).getBoundingClientRect();
     boundaries += Math.round(r.left) + ',' + Math.round(r.top) + ',' + Math.round(r.right) + ',' + Math.round(r.bottom) + ';';
});

return boundaries;
