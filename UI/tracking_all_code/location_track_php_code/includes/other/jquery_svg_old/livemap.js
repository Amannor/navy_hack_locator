var svgns = "http://www.w3.org/2000/svg";
var svgroot;

//Init 'Unobtrusive' JavaScript
addEvent(window, "load", function() {




timeout = window.setTimeout("timedout();", 100);


	svgroot = document.createElementNS(svgns, "svg");
	//svgroot.setAttribute("width", "100%");
	//svgroot.setAttribute("height", "100%");
	svgroot.setAttribute("viewBox", "0 0 320 200");


	$("livemapsvg").appendChild(svgroot);



   rect = document.createElementNS(svgns, "rect");
    rect.setAttributeNS(null, "y", "45");
    rect.setAttributeNS(null, "width", "10");
    rect.setAttributeNS(null, "height", "10");
    rect.setAttributeNS(null, "fill", "green");

    
    svgroot.appendChild(rect);



    animateX = document.createElementNS(svgns, "animate");
    animateX.setAttributeNS(null, "attributeName", "x");
    animateX.setAttributeNS(null, "from", "0");
    animateX.setAttributeNS(null, "to", "90");
    animateX.setAttributeNS(null, "dur", "10s");
    animateX.setAttributeNS(null, "repeatCount", "indefinite");
    rect.appendChild(animateX);


/*
	var circleNode = document.createElementNS(svgns, "circle");

	circleNode.setAttribute("style","fill:blue");
	circleNode.setAttribute("cx", "100");
	circleNode.setAttribute("cy", "100");
	circleNode.setAttribute("r",  "100");

	svgroot.appendChild(circleNode);
*/


/*
   rect = document.createElementNS(svgns, "rect");
    rect.setAttributeNS(null, "y", "45");
    rect.setAttributeNS(null, "width", "10");
    rect.setAttributeNS(null, "height", "10");
    rect.setAttributeNS(null, "fill", "green");
    
    animateX = document.createElementNS(svgns, "animate");
    animateX.setAttributeNS(null, "attributeName", "x");
    animateX.setAttributeNS(null, "from", "0");
    animateX.setAttributeNS(null, "to", "90");
    animateX.setAttributeNS(null, "dur", "10s");
    animateX.setAttributeNS(null, "repeatCount", "indefinite");
    
    rect.appendChild(animateX);

    svgroot.documentElement.appendChild(rect)

*/




/*
var SVGDoc=document;
var txt=SVGDoc.getElementById("txt");
var link=SVGDoc.createElement("a");
var text_node=SVGDoc.createTextNode("LINK");

link.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href","http://www.w3schools.com");

link.appendChild(text_node);
txt.appendChild(link);
*/

/*
   rect = document.createElementNS(svgns, "rect");
    rect.setAttributeNS(null, "y", "45");
    rect.setAttributeNS(null, "width", "10");
    rect.setAttributeNS(null, "height", "10");
    rect.setAttributeNS(null, "fill", "green");
    
    animateX = document.createElementNS(svgns, "animate");
    animateX.setAttributeNS(null, "attributeName", "x");
    animateX.setAttributeNS(null, "from", "0");
    animateX.setAttributeNS(null, "to", "90");
    animateX.setAttributeNS(null, "dur", "10s");
    animateX.setAttributeNS(null, "repeatCount", "indefinite");
    
    svgroot.appendChild(animateX);
*/


/*
	var circleNode = document.createElementNS(svgns, "rect");

	circleNode.setAttribute("x","10");
	circleNode.setAttribute("y", "10");
	circleNode.setAttribute("height", "110");
	circleNode.setAttribute("width",  "110");
	circleNode.setAttribute("style",  "stroke:#ff0000; fill: #0000ff");
	circleNode.setAttribute("onclick",  "fade(getTarget(evt))");

	svgroot.appendChild(circleNode);



fade (circleNode)
*/


/*

	var circleNode1 = document.createElementNS(svgns, "animateColor");
	circleNode1.setAttribute("attributeName","fill");
	circleNode1.setAttribute("attributeType", "CSS");
	circleNode1.setAttribute("from", "lime");
	circleNode1.setAttribute("to",  "red");
	circleNode1.setAttribute("begin",  "2s");
	circleNode1.setAttribute("dur",  "4s");
	circleNode1.setAttribute("fill",  "freeze");

	circleNode.appendChild(circleNode1);
*/

//fade (circleNode);


/*
	var circleNode1 = document.createElementNS(svgns, "animateTransform");
	circleNode1.setAttribute("attributeName","transform");
	circleNode1.setAttribute("begin", "0s");
	circleNode1.setAttribute("dur", "20s");
	circleNode1.setAttribute("type",  "rotate");
	circleNode1.setAttribute("from",  "0 60 60");
	circleNode1.setAttribute("to",  "360 60 60");
	circleNode1.setAttribute("repeatCount",  "indefinite");
*/


/*
    <rect x="10" y="10" height="110" width="110"
         style="stroke:#ff0000; fill: #0000ff">
    
        <animateTransform
            attributeName="transform"
            begin="0s"
            dur="20s"
            type="rotate"
            from="0 60 60"
            to="360 60 60"
            repeatCount="indefinite" 
        />
    </rect>
*/






/*
	var div = document.createElement('div');
	div.style.textAlign = 'center';
	div.style.verticalAlign = 'middle';
	$("livemapsvg").appendChild(div);


	root = document.createElementNS("http://www.w3.org/2000/svg", "svg");
	root.setAttribute("width", "100%");
	root.setAttribute("height", "100%");
	root.setAttribute("viewBox", "0 0 320 200");
	root.addEventListener("SVGLoad", function(evt) {
	  // do something
	}, false);
	svgweb.appendChild(root, div);
*/


});


function timedout() {






}



    function getTarget (event) {
      var target = event.target;
      while (target.parentNode !== event.currentTarget) {
        target = target.parentNode;
      }
      return target;
    }
 
function  fade (target) {
    // create the fade animation
    var animation = document.createElementNS(
                         'http://www.w3.org/2000/svg', 'animate');
    animation.setAttributeNS(null, 'attributeName', 'fill-opacity');
    animation.setAttributeNS(null, 'begin', 'indefinite');
    animation.setAttributeNS(null, 'to', 0);
    animation.setAttributeNS(null, 'dur', 0.25);
    animation.setAttributeNS(null, 'fill', 'freeze');
    // link the animation to the target
    target.appendChild(animation);
    // start the animation
}
