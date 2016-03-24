/*
http://www.webpagetest.org/?iq=80

Script:
setViewportSize 1280 1024
navigate http://www.sergeychernyshev.com/

XML Result:
http://www.webpagetest.org/xmlResult/160301_W9_165/
*/

// [selector-boundaries]

// sergeychernyshev.com
// [selector-boundaries]
/*
var selectors = [
   '#photo img',
   '#title',
   '.section:nth-child(4)',
   '.section a:nth-child(2) img'
];
*/


// bedbathandbeyond.com
// [selector-boundaries]
/*
var selectors = [
  '#siteLogo img',
  '#collegeBridalArea',
  '#searchForm'
]
*/

// howdoable.com
// [selector-boundaries]
var selectors = [
  '.brand img, h1',
  '.well',
  '.offset2 h2'
];

function getScrollBarWidth() {
	var inner = document.createElement('p');
	inner.style.width = "100%";
	inner.style.height = "200px";

	var outer = document.createElement('div');
	outer.style.position = "absolute";
	outer.style.top = "0px";
	outer.style.left = "0px";
	outer.style.visibility = "hidden";
	outer.style.width = "200px";
	outer.style.height = "150px";
	outer.style.overflow = "hidden";
	outer.appendChild(inner);

	document.body.appendChild(outer);
	var w1 = inner.offsetWidth;
	outer.style.overflow = 'scroll';
	var w2 = inner.offsetWidth;

	if (w1 == w2) {
		w2 = outer.clientWidth;
	}

	document.body.removeChild(outer);

	return (w1 - w2);
}

var metric = {
  viewport_width: document.documentElement.clientWidth,
  scrollbar_width: getScrollBarWidth(),
  boundaries: []
};

selectors.forEach(function(selector) {
  var boxes = document.querySelectorAll(selector);
  var count = boxes.length;

  for (var i = 0; i < count; i++) {
    var coords = boxes[i].getBoundingClientRect()
    metric.boundaries.push({
      left: coords.left,
      top: coords.top,
      right: coords.right,
      bottom: coords.bottom
    });
  }
});

return JSON.stringify(metric);
