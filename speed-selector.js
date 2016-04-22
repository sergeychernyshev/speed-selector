/*
http://www.webpagetest.org/?iq=100

Script:
setViewportSize 1280 1024
navigate http://www.sergeychernyshev.com/

XML Result:
http://www.webpagetest.org/xmlResult/160301_W9_165/
*/

// [selector-boundaries]
var selector_boundaries = function(zones) {
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
        zones: []
    };

    zones.forEach(function(zone) {
        var boundaries = [];

        zone.selectors.forEach(function(selector) {
            var boxes = document.querySelectorAll(selector);
            var count = boxes.length;
            for (var i = 0; i < count; i++) {
                var coords = boxes[i].getBoundingClientRect();
                boundaries.push({
                    left: coords.left,
                    top: coords.top,
                    right: coords.right,
                    bottom: coords.bottom
                });
            }

        });

        metric.zones.push({
            slug: zone.slug,
            name: zone.name,
            boundaries: boundaries
        });
    });

    return JSON.stringify(metric);
};

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

// cnn.com
return selector_boundaries([{
    slug: 'basic',
    name: 'Basic branding',
    selectors: ['#logo']
}, {
    slug: 'primary',
    name: 'Primary content',
    selectors: ['#homepage1-zone-1 .media__image--responsive']
}, {
    slug: 'nav',
    name: 'Main navigation',
    selectors: ['#logo', '.nav-menu-links', '#menu', '#search-button', '#nav-mobileTV']
}]);
