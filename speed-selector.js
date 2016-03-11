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

var metric = {
  viewport_width: document.documentElement.clientWidth,
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
